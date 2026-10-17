<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientQuotaException;
use App\Jobs\SendETicketJob;
use App\Models\Order;
use App\Models\Seat;
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
            'seat_ids'           => 'nullable|array|max:' . self::MAX_QTY_PER_ORDER,
            'seat_ids.*'         => 'integer|distinct',
        ]);

        $requestedQuantity = (int) $request->input('quantity', 1);
        $seatIds           = collect($request->input('seat_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $merchantRef       = 'TKT-' . strtoupper(Str::random(16));

        // Semua pengecekan + penulisan data dibungkus 1 transaction dengan
        // row lock di baris TicketCategory. Karena SEMUA checkout untuk kategori
        // yang sama harus antre di kunci ini, dua orang yang rebutan tiket
        // terakhir (atau kursi yang sama) tidak mungkin lolos berbarengan.
        try {
            [$order, $category, $amount, $quantity] = DB::transaction(
                function () use ($request, $requestedQuantity, $seatIds, $merchantRef) {
                    $category = TicketCategory::where('id', $request->ticket_category_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $category) {
                        throw new InsufficientQuotaException('Kategori tiket tidak ditemukan.');
                    }

                    $isSeated = $category->seating_mode === 'seated';

                    // Kategori "seated": jumlah tiket = jumlah kursi yang dipilih.
                    // (Angka quantity dari form diabaikan, tidak dipercaya.)
                    $quantity = $isSeated ? $seatIds->count() : $requestedQuantity;

                    if ($isSeated && $quantity < 1) {
                        throw new InsufficientQuotaException('Pilih minimal 1 kursi dulu di denah.');
                    }

                    if ($category->quota < $quantity) {
                        throw new InsufficientQuotaException("Maaf, sisa kuota tiket ini cuma {$category->quota}.");
                    }

                    $seats = collect();

                    if ($isSeated) {
                        $seats = Seat::where('ticket_category_id', $category->id)
                            ->whereIn('id', $seatIds)
                            ->get();

                        if ($seats->count() !== $seatIds->count()) {
                            throw new InsufficientQuotaException('Kursi yang dipilih tidak valid. Muat ulang halaman lalu coba lagi.');
                        }

                        $takenIds = Ticket::whereIn('seat_id', $seatIds)->pluck('seat_id');

                        if ($takenIds->isNotEmpty()) {
                            $labels = $seats->whereIn('id', $takenIds)->pluck('label')->implode(', ');
                            throw new InsufficientQuotaException("Kursi {$labels} baru saja dipesan orang lain. Pilih kursi lain ya.");
                        }
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

                    // Satu baris Ticket per tiket. Untuk kategori seated, tiap tiket
                    // menunjuk ke satu kursi. Pakai unguarded supaya seat_id pasti
                    // tersimpan walau $fillable di model Ticket belum diperbarui.
                    $ticketSeatIds = $isSeated ? $seats->pluck('id')->all() : array_fill(0, $quantity, null);

                    foreach ($ticketSeatIds as $seatId) {
                        $attributes = [
                            'order_id'           => $order->id,
                            'ticket_category_id' => $category->id,
                            'ticket_code'        => 'QR-' . strtoupper(Str::random(8)),
                            'status'             => 'PENDING',
                        ];

                        if ($seatId !== null) {
                            $attributes['seat_id'] = $seatId;
                        }

                        Ticket::unguarded(fn () => Ticket::create($attributes));
                    }

                    $category->decrement('quota', $quantity);

                    return [$order, $category, $amount, $quantity];
                }
            );
        } catch (InsufficientQuotaException $e) {
            return back()->with('error', $e->getMessage())->withInput();
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

        // Tripay gagal: batalkan semuanya (kuota, tiket, kursi kembali bebas).
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
