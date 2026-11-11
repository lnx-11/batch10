<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->after('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1)->after('product_id');
            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'product_id']);
            $table->dropConstrainedForeignId('product_id');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('quantity');
        });
    }
};
