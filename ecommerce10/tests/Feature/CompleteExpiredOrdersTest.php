<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed();
});

test('verified processing invoices are completed after five days but other invoices are unchanged', function () {
    Carbon::setTestNow('2026-11-11 12:00:00');
    $customer = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $oldInvoice = 'INV-20261111-00000051';
    $recentInvoice = 'INV-20261111-00000052';
    $pendingInvoice = 'INV-20261111-00000053';
    $cancelledInvoice = 'INV-20261111-00000054';

    foreach ([
        [$oldInvoice, 'processing', 'paid', now()->subDays(5)->subMinute()],
        [$recentInvoice, 'processing', 'paid', now()->subDays(4)],
        [$pendingInvoice, 'pending', 'unpaid', now()->subDays(8)],
        [$cancelledInvoice, 'cancelled', 'unpaid', now()->subDays(8)],
    ] as [$invoiceNumber, $status, $paymentStatus, $verifiedAt]) {
        $customer->orders()->create([
            'invoice_number' => $invoiceNumber,
            'product_id' => $product->id,
            'quantity' => 1,
            'total' => $product->price,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_verified_at' => $verifiedAt,
        ]);
    }

    $this->artisan('orders:complete-expired')
        ->expectsOutput('1 invoice otomatis diubah menjadi Completed.')
        ->assertExitCode(0);

    $this->assertDatabaseHas('orders', ['invoice_number' => $oldInvoice, 'status' => 'completed']);
    $this->assertDatabaseHas('orders', ['invoice_number' => $recentInvoice, 'status' => 'processing']);
    $this->assertDatabaseHas('orders', ['invoice_number' => $pendingInvoice, 'status' => 'pending']);
    $this->assertDatabaseHas('orders', ['invoice_number' => $cancelledInvoice, 'status' => 'cancelled']);

    Carbon::setTestNow();
});

test('expired invoice is completed as a whole only when all items remain eligible', function () {
    Carbon::setTestNow('2026-11-11 12:00:00');
    $customer = User::factory()->create();
    $headphones = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $lamp = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000055';

    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $headphones->id,
        'quantity' => 1,
        'total' => $headphones->price,
        'status' => 'processing',
        'payment_status' => 'paid',
        'payment_verified_at' => now()->subDays(6),
    ]);
    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $lamp->id,
        'quantity' => 1,
        'total' => $lamp->price,
        'status' => 'processing',
        'payment_status' => 'paid',
        'payment_verified_at' => now()->subDays(4),
    ]);

    $this->artisan('orders:complete-expired')
        ->expectsOutput('0 invoice otomatis diubah menjadi Completed.')
        ->assertExitCode(0);

    expect($customer->orders()->where('invoice_number', $invoiceNumber)->where('status', 'processing')->count())->toBe(2);

    Carbon::setTestNow();
});
