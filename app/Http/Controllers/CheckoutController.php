<?php

namespace App\Http\Controllers;

use App\Jobs\SendETicketJob;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Batas jumlah tiket maksimal per transaksi.
     * Samakan angka ini dengan batas stepper di show.blade.php.
     */
    private const MAX_QTY_PER_ORDER = 10;

    /**
     * Memproses Form Checkout dan Mengirim Transaksi ke Tripay.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Form
        $request->validate([
            'customer_name'      => 'required|string|max:255',
            'customer_email'     => 'required|email',
            'customer_phone'     => 'required|numeric',
            'ticket_category_id' => 'required|exists:ticket_categories,id',
            'payment_method'     => 'required|string',
            'quantity'           => 'nullable|integer|min:1|max:' . self::MAX_QTY_PER_ORDER,
        ]);

        // Server yang menentukan quantity final — JANGAN percaya begitu saja
        // angka dari form, siapa pun bisa kirim request manual dengan angka lain.
        $quantity = (int) $request->input('quantity', 1);

        // 2. Cek Sisa Kuota Tiket
        $category = TicketCategory::findOrFail($request->ticket_category_id);
        if ($category->quota < $quantity) {
            return back()->with('error', "Maaf, sisa kuota tiket ini cuma {$category->quota}.");
        }

        // Sebelumnya: 'TKT-' . time() . Str::random(3) — bagian time() gampang
        // ditebak rentangnya, dan cuma 3 karakter acak (~238rb kombinasi) bisa
        // di-brute-force. Sekarang murni acak dengan keyspace yang jauh lebih besar
        // (62^16 kombinasi), sekaligus jadi identifier publik di URL e-tiket
        // (route ticket.show), jadi WAJIB tidak gampang ditebak.
        $merchantRef = 'TKT-' . strtoupper(Str::random(16));
        $amount      = $category->price * $quantity; // total, bukan harga satuan

        // 3-5. Simpan Order + N baris Tiket + potong kuota, dibungkus transaction
        // supaya kalau salah satu langkah gagal, semuanya batal bareng (tidak
        // ada order "nyangkut" tanpa tiket, atau tiket tanpa order).
        try {
            $order = DB::transaction(function () use ($request, $category, $quantity, $merchantRef, $amount) {
                $order = Order::create([
                    'merchant_ref'   => $merchantRef,
                    'customer_name'  => $request->customer_name,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'total_amount'   => $amount,
                    'payment_method' => $request->payment_method,
                    'status'         => 'UNPAID',
                ]);

                // Buat SATU baris Ticket untuk SETIAP tiket yang dibeli,
                // masing-masing dengan ticket_code unik sendiri.
                for ($i = 0; $i < $quantity; $i++) {
                    Ticket::create([
                        'order_id'           => $order->id,
                        'ticket_category_id' => $category->id,
                        'ticket_code'        => 'QR-' . strtoupper(Str::random(8)),
                        'status'             => 'PENDING',
                    ]);
                }

                $category->decrement('quota', $quantity);

                return $order;
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal menyimpan pesanan, coba lagi.');
        }

        // 6. Siapkan Data & Signature untuk API Tripay
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
                    'price'    => $category->price, // harga SATUAN
                    'quantity' => $quantity,         // dikali otomatis oleh Tripay
                ],
            ],
            'return_url'   => route('home'),
            'expired_time' => time() + (24 * 60 * 60), // Expired 24 Jam
            'signature'    => $signature,
        ];

        // 7. Tembak API Tripay
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

        // 8. Kalau Tripay gagal: batalkan SEMUA yang tadi dibuat (bukan cuma
        // kembalikan kuota, tapi juga hapus order+tiket "PENDING" yang nyangkut).
        DB::transaction(function () use ($order, $category, $quantity) {
            $category->increment('quota', $quantity);
            Ticket::where('order_id', $order->id)->delete();
            $order->delete();
        });

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

        // 1. Jika Pembayaran Lunas
        if ($data->status === 'PAID' && $order->status === 'UNPAID') {
            $order->update(['status' => 'PAID']);

            // Aktifkan SEMUA tiket dalam order ini (bukan cuma satu)
            Ticket::where('order_id', $order->id)->update(['status' => 'AVAILABLE']);

            SendETicketJob::dispatch($order->id)->afterCommit();
        }
        // 2. Jika Pembayaran Batal/Expired
        elseif (in_array($data->status, ['EXPIRED', 'FAILED']) && $order->status === 'UNPAID') {
            $order->update(['status' => $data->status]);

            // Kembalikan kuota SESUAI JUMLAH tiket yang ada di order ini,
            // bukan cuma +1 — lalu hapus semua tiket PENDING-nya.
            $tickets = Ticket::where('order_id', $order->id)->get();
            if ($tickets->isNotEmpty()) {
                TicketCategory::where('id', $tickets->first()->ticket_category_id)
                    ->increment('quota', $tickets->count());

                Ticket::where('order_id', $order->id)->delete();
            }
        }

        return response()->json(['success' => true]);
    }
}
