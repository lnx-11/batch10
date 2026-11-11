<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_number', 32)->nullable()->after('id');
            $table->index('invoice_number');
        });

        DB::table('orders')
            ->select(['id', 'created_at'])
            ->orderBy('id')
            ->each(function (object $order): void {
                $date = $order->created_at
                    ? Carbon::parse($order->created_at)->format('Ymd')
                    : now()->format('Ymd');

                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['invoice_number' => sprintf('INV-%s-%08d', $date, $order->id)]);
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['invoice_number']);
            $table->dropColumn('invoice_number');
        });
    }
};
