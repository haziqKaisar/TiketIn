<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Console\Command;

class ExpireStaleOrders extends Command
{
    protected $signature = 'orders:expire-stale {--hours=24 : Order UNPAID lebih tua dari sekian jam dianggap kadaluarsa}';

    protected $description = 'Tandai order UNPAID yang sudah lewat batas waktu sebagai EXPIRED dan kembalikan kuotanya';

    public function handle(OrderService $orderService): int
    {
        $hours = (int) $this->option('hours');

        $staleOrders = Order::where('status', 'UNPAID')
            ->where('created_at', '<=', now()->subHours($hours))
            ->get();

        if ($staleOrders->isEmpty()) {
            $this->info('Tidak ada order kadaluarsa saat ini.');
            return self::SUCCESS;
        }

        foreach ($staleOrders as $order) {
            $orderService->releaseUnpaidOrder($order, 'EXPIRED');
            $this->line("Order {$order->merchant_ref} ditandai EXPIRED, kuota dikembalikan.");
        }

        $this->info("Selesai: {$staleOrders->count()} order dibersihkan.");

        return self::SUCCESS;
    }
}
