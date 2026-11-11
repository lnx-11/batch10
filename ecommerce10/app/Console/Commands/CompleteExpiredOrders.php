<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteExpiredOrders extends Command
{
    protected $signature = 'orders:complete-expired';

    protected $description = 'Complete verified processing invoices after five days without receipt confirmation';

    public function handle(): int
    {
        $cutoff = now()->subDays(5);
        $invoiceNumbers = Order::query()
            ->where('status', Order::STATUS_PROCESSING)
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereNotNull('payment_verified_at')
            ->where('payment_verified_at', '<', $cutoff)
            ->distinct()
            ->orderBy('invoice_number')
            ->pluck('invoice_number');
        $completedCount = 0;

        foreach ($invoiceNumbers as $invoiceNumber) {
            $completed = DB::transaction(function () use ($invoiceNumber, $cutoff): bool {
                $orders = Order::query()
                    ->where('invoice_number', $invoiceNumber)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($orders->isEmpty() || $orders->contains(
                    fn (Order $order): bool => $order->status !== Order::STATUS_PROCESSING
                        || $order->payment_status !== Order::PAYMENT_PAID
                        || $order->payment_verified_at === null
                        || $order->payment_verified_at->isAfter($cutoff),
                )) {
                    return false;
                }

                Order::query()
                    ->where('invoice_number', $invoiceNumber)
                    ->update(['status' => Order::STATUS_COMPLETED]);

                return true;
            });

            if ($completed) {
                $completedCount++;
            }
        }

        $this->info("{$completedCount} invoice otomatis diubah menjadi Completed.");

        return self::SUCCESS;
    }
}
