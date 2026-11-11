<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('total');
            $table->index(['invoice_number', 'status']);
        });

        DB::table('orders')->update(['status' => 'processing']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['invoice_number', 'status']);
            $table->dropColumn('status');
        });
    }
};
