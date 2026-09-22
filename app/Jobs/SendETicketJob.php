<?php

namespace App\Jobs;

use App\Mail\ETicketMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendETicketJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 60;
    public array $backoff = [30, 120, 600, 1800];

    public function __construct(public int $orderId) {}

    public function uniqueId(): string { return 'eticket-' . $this->orderId; }
    public function uniqueFor(): int { return 3600; }

    public function handle(): void
    {
        $order = Order::with('tickets.ticketCategory.event')->find($this->orderId);

        if (! $order || $order->status !== 'PAID' || $order->eticket_sent_at !== null) {
            return;
        }
        if ($order->tickets->isEmpty()) {
            Log::warning('E-ticket dibatalkan: order tanpa tiket.', ['order_id' => $order->id]);
            return;
        }

        Mail::to($order->customer_email, $order->customer_name)->send(new ETicketMail($order));
        $order->forceFill(['eticket_sent_at' => now()])->saveQuietly();
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Gagal kirim e-ticket.', ['order_id' => $this->orderId, 'error' => $e->getMessage()]);
    }
}
