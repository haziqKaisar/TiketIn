<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientQuotaException;
use App\Jobs\SendETicketJob;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    private const MAX_QTY_PER_ORDER = 10;

    /**
     * Memproses Form Checkout dan Mengirim Transaksi ke Tripay.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_email'     => 'required|email',
            'customer_phone'     => 'required|numeric',
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'payment_method'     => 'required|string',
            'quantity'           => 'nullable|integer|min:1|max:' . self::MAX_QTY_PER_ORDER,
        ]);

        $quantity    = (int) $request->input('quantity', 1);
        $merchantRef = 'TKT-' . strtoupper(Str::random(16));

        // Semua pengecekan + penulisan data dibungkus 1 transaction dengan
        // row lock (lockForUpdate) di baris TicketCategory-nya. Ini yang
        // mencegah race condition: kalau 2 orang checkout tiket terakhir
        // yang sama nyaris bersamaan, permintaan KEDUA akan menunggu
        // sampai transaction PERTAMA selesai (commit/rollback) sebelum
        // dia sendiri baca ulang sisa kuota — jadi tidak mungkin dua-duanya
        // lolos pengecekan kuota yang sama.
        try {
            [$order, $category, $amount] = DB::transaction(function () use ($request, $quantity, $merchantRef) {
                $category = TicketCategory::where('id', $request->ticket_category_id)
                    ->lockForUpdate()
                    ->first();

                if (! $category) {
                    throw new InsufficientQuotaException('Kategori tiket tidak ditemukan.');
                }

                if ($category->quota < $quantity) {
                    throw new InsufficientQuotaException("Maaf, sisa kuota tiket ini cuma {$category->quota}.");
                }

                $amount = $category->price * $quantity;

                $order = Order::create([
                    'merchant_ref'   => $merchantRef,
                    'customer_name'  => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'total_amount'   => $amount,
                    'payment_method' => $request->payment_method,
                    'status'         => 'UNPAID',
                ]);

                for ($i = 0; $i < $quantity; $i++) {
                    Ticket::create([
                        'order_id'           => $order->id,
                        'ticket_category_id' => $category->id,
                        'ticket_code'        => 'QR-' . strtoupper(Str::random(8)),
                        'status'             => 'PENDING',
                    ]);
                }

                // Masih di dalam transaction & row masih terkunci -- aman.
                $category->decrement('quota', $quantity);

                return [$order, $category, $amount];
            });
        } catch (InsufficientQuotaException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal menyimpan pesanan, coba lagi.');
        }

        // Siapkan Data & Signature untuk API Tripay
        $apiKey       = env('TRIPAY_API_KEY');
        $privateKey   = env('TRIPAY_PRIVATE_KEY');
        $merchantCode = env('TRIPAY_MERCHANT_CODE');
        $endpoint     = env('TRIPAY_URL') . 'transaction/create';

        $signature = hash_hmac('sha256', $merchantCode . $merchantRef . $amount, $privateKey);

        $payload = [
            'method'         => $request->payment_method,
            'merchant_ref'   => $merchantRef,
            'amount'         => $amount,
            'customer_name'  => $request->customer_name,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'order_items'    => [
                [
                    'sku'      => 'TKT-' . $category->id,
                    'name'     => 'Tiket: ' . $category->name,
                    'price'    => $category->price,
                    'quantity' => $quantity,
                ],
            ],
            'return_url'   => route('home'),
            'expired_time' => time() + (24 * 60 * 60),
            'signature'    => $signature,
        ];

        $response = Http::asForm()->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
        ])->post($endpoint, $payload);

        if ($response->successful()) {
            $result = $response->json();
            if (!empty($result['success']) && $result['success'] === true) {
                $order->update(['checkout_url' => $result['data']['checkout_url']]);
                return redirect($result['data']['checkout_url']);
            }
        }

        // Tripay gagal: batalkan semua yang tadi dibuat (kuota, order, tiket)
        // lewat service yang sama dipakai webhook & cleanup job.
        app(OrderService::class)->releaseUnpaidOrder($order, 'FAILED');

        $errorMessage = $response->json('message') ?? 'Gagal terhubung ke server pembayaran.';

        return back()->with('error', 'Gagal terhubung ke Tripay. Pesan: ' . $errorMessage);
    }

    /**
     * Menerima Webhook / Callback dari Tripay.
     */
    public function callback(Request $request)
    {
        $privateKey        = env('TRIPAY_PRIVATE_KEY');
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json              = $request->getContent();

        $signature = hash_hmac('sha256', $json, $privateKey);
        if ($signature !== $callbackSignature) {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        if ($request->server('HTTP_X_CALLBACK_EVENT') !== 'payment_status') {
            return response()->json(['success' => false, 'message' => 'Unrecognized event'], 400);
        }

        $data = json_decode($json);

        $order = Order::where('merchant_ref', $data->merchant_ref)->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if ($data->status === 'PAID' && $order->status === 'UNPAID') {
            $order->update(['status' => 'PAID']);
            Ticket::where('order_id', $order->id)->update(['status' => 'AVAILABLE']);
            SendETicketJob::dispatch($order->id)->afterCommit();
        } elseif (in_array($data->status, ['EXPIRED', 'FAILED']) && $order->status === 'UNPAID') {
            app(OrderService::class)->releaseUnpaidOrder($order, $data->status);
        }

        return response()->json(['success' => true]);
    }
}
