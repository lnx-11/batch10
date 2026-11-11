<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->where('status', 'processing')
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        // A processing status cannot be restored without evidence of verification.
    }
};
