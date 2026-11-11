<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::factory()->admin()->create());
});

test('dashboard shows product and category totals and the aggregate product clicks', function () {
    $response = $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Jumlah Produk')
        ->assertSee((string) Product::count())
        ->assertSee('Jumlah Kategori')
        ->assertSee((string) ProductCategory::count())
        ->assertSee('Users')
        ->assertSee((string) User::count())
        ->assertSee('Revenue')
        ->assertSee('Rp 0')
        ->assertSee('Jumlah Klik Produk')
        ->assertSee('0')
        ->assertSee('Analisis Toko')
        ->assertSee('Pesanan 7 Hari Terakhir')
        ->assertSee('Klik Produk per Kategori')
        ->assertSee('data-dashboard-orders', false)
        ->assertSee('Data contoh grafik — belum ada pesanan dalam 7 hari terakhir')
        ->assertSee('Latest Orders')
        ->assertSee('Geser tabel ke samping')
        ->assertSee('untuk melihat kolom lainnya.')
        ->assertSee('admin-orders-scroll', false)
        ->assertSee('min-w-[1120px]', false)
        ->assertSee('Data contoh pesanan — belum ada pesanan sebenarnya')
        ->assertSee('Data contoh status — belum ada pesanan sebenarnya')
        ->assertSee('data-dashboard-order-status', false)
        ->assertSee('INV-6339')
        ->assertSee('Agus Setiawan')
        ->assertSee('Pending')
        ->assertSee('Status Pembayaran')
        ->assertSee('Data contoh klik — belum ada klik sebenarnya')
        ->assertSee('data-dashboard-clicks', false)
        ->assertSee('data-values="[18,12,9,6]"', false);

    $sampleOrders = $response->viewData('latestOrders');
    $orderChart = $response->viewData('orderChart');

    expect($orderChart->sum('orders'))->toBe($sampleOrders->count())
        ->and($orderChart->sum('revenue'))->toBe($sampleOrders->sum('total'));

    $startDate = now()->startOfDay()->subDays(6);
    $expectedDailyTotals = collect(range(0, 6))->map(function (int $offset) use ($startDate, $sampleOrders) {
        $date = $startDate->copy()->addDays($offset)->toDateString();
        $ordersForDay = $sampleOrders->filter(
            fn (array $order) => $order['created_at']->toDateString() === $date
        );

        return [
            'orders' => $ordersForDay->count(),
            'revenue' => $ordersForDay->sum('total'),
        ];
    });

    expect($orderChart->map(fn (array $day) => ['orders' => $day['orders'], 'revenue' => $day['revenue']])->all())
        ->toBe($expectedDailyTotals->all());
});

test('product clicks are recorded and the latest product orders appear on the dashboard', function () {
    $user = User::factory()->create(['name' => 'Pelanggan Terbaru']);
    $earlierProduct = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $latestProduct = Product::where('slug', 'kamera-pocket-35')->firstOrFail();

    $this->postJson(route('products.click', $latestProduct))
        ->assertOk()
        ->assertJsonPath('message', 'Klik produk dicatat.');

    expect($latestProduct->fresh()->click_count)->toBe(1);

    DB::table('orders')->insert([
        [
            'invoice_number' => 'INV-20261110-00000001',
            'user_id' => $user->id,
            'product_id' => $earlierProduct->id,
            'quantity' => 1,
            'total' => $earlierProduct->price,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        [
            'invoice_number' => 'INV-20261111-00000002',
            'user_id' => $user->id,
            'product_id' => $latestProduct->id,
            'quantity' => 2,
            'total' => $latestProduct->price * 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-dashboard-orders', false)
        ->assertSeeInOrder([
            'INV-20261111-00000002',
            'Kamera Pocket 35',
            'Pelanggan Terbaru',
            'INV-20261110-00000001',
            'Headphone Studio One',
        ])
        ->assertSee('Rp '.number_format($latestProduct->price * 2, 0, ',', '.'))
        ->assertSee('data-dashboard-clicks', false)
        ->assertDontSee('Data contoh pesanan — belum ada pesanan sebenarnya')
        ->assertDontSee('Data contoh klik — belum ada klik sebenarnya')
        ->assertSee('data-dashboard-order-status', false)
        ->assertSee('data-dashboard-order-status', false);
});

test('dashboard groups multiple product lines under one invoice', function () {
    $user = User::factory()->create(['name' => 'Pelanggan Satu Invoice']);
    $headphones = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $lamp = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000020';

    $user->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $headphones->id,
        'quantity' => 1,
        'total' => $headphones->price,
    ]);
    $user->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $lamp->id,
        'quantity' => 2,
        'total' => $lamp->price * 2,
    ]);

    $response = $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee($invoiceNumber)
        ->assertSee('Headphone Studio One, Lampu Meja Arc')
        ->assertSee('Pelanggan Satu Invoice')
        ->assertSee('Rp '.number_format($headphones->price + $lamp->price * 2, 0, ',', '.'));

    expect($response->viewData('latestOrders')
        ->filter(fn (array $invoice) => $invoice['id'] === $invoiceNumber)
        ->count())->toBe(1);
});

test('dashboard order status chart counts unique invoices by status', function () {
    $user = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $invoices = [
        ['INV-20261111-00000030', 'pending'],
        ['INV-20261111-00000031', 'cancelled'],
        ['INV-20261111-00000032', 'processing'],
    ];

    foreach ($invoices as [$invoiceNumber, $status]) {
        $user->orders()->create([
            'invoice_number' => $invoiceNumber,
            'product_id' => $product->id,
            'quantity' => 1,
            'total' => $product->price,
            'status' => $status,
        ]);
    }

    $response = $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-labels="[&quot;Pending&quot;,&quot;Processing&quot;,&quot;Completed&quot;,&quot;Cancelled&quot;]"', false)
        ->assertSee('data-values="[1,1,0,1]"', false)
        ->assertSee('<span class="text-xs font-medium text-red-700" role="status">Pesanan Dibatalkan</span>', false)
        ->assertDontSee('admin-order-action-cancelled', false)
        ->assertDontSee('Data contoh status — belum ada pesanan sebenarnya');

    expect($response->viewData('revenueTotal'))->toBe($product->price * 2)
        ->and($response->viewData('latestOrders')->firstWhere('id', $invoices[1][0])['is_cancelled'])->toBeTrue();
});

test('admin can manually verify payment for all items on a pending invoice', function () {
    $customer = User::factory()->create(['name' => 'Pelanggan Verifikasi']);
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $anotherProduct = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000040';

    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);
    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $anotherProduct->id,
        'quantity' => 2,
        'total' => $anotherProduct->price * 2,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $dashboard = $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Belum diverifikasi')
        ->assertSee(route('dashboard.orders.accept', $invoiceNumber), false)
        ->assertSee(route('dashboard.orders.cancel', $invoiceNumber), false)
        ->assertSee('Terima pesanan')
        ->assertSee('admin-order-action-cancel', false)
        ->assertSee('Batalkan');
    expect($dashboard->viewData('latestOrders')->firstWhere('id', $invoiceNumber)['can_verify_payment'])->toBeTrue();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->post(route('dashboard.orders.accept', $invoiceNumber))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Invoice '.$invoiceNumber.' diterima. Status pesanan menjadi Processing dan pembayaran dicatat terverifikasi secara manual.');

    $orders = $customer->orders()->where('invoice_number', $invoiceNumber)->get();
    expect($orders)->toHaveCount(2);
    foreach ($orders as $order) {
        expect($order->status)->toBe('processing')
            ->and($order->payment_status)->toBe('paid')
            ->and($order->payment_verified_by)->toBe($admin->id)
            ->and($order->payment_verified_at)->not->toBeNull();
    }

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Terverifikasi')
        ->assertDontSee(route('purchases.complete', $invoiceNumber), false)
        ->assertDontSee(route('dashboard.orders.accept', $invoiceNumber), false)
        ->assertDontSee('Terima pesanan')
        ->assertDontSee('Batalkan');

    $this->actingAs($customer)
        ->get(route('purchases.show', $invoiceNumber))
        ->assertOk()
        ->assertSee('Terverifikasi')
        ->assertSee('Diverifikasi admin '.$admin->name);
});

test('only admins can accept orders and cancelled invoices cannot be accepted', function () {
    $customer = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000041';
    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        'status' => 'cancelled',
        'payment_status' => 'unpaid',
    ]);

    $this->actingAs($customer)
        ->post(route('dashboard.orders.accept', $invoiceNumber))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('dashboard.orders.accept', $invoiceNumber))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('orders', [
        'invoice_number' => $invoiceNumber,
        'status' => 'cancelled',
        'payment_status' => 'unpaid',
        'payment_verified_by' => null,
    ]);
});

test('admin can cancel a pending invoice and restore every item stock', function () {
    $customer = User::factory()->create();
    $headphones = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $lamp = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000042';
    $headphones->decrement('stock', 1);
    $lamp->decrement('stock', 2);
    $headphoneStock = $headphones->stock;
    $lampStock = $lamp->stock;

    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $headphones->id,
        'quantity' => 1,
        'total' => $headphones->price,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);
    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $lamp->id,
        'quantity' => 2,
        'total' => $lamp->price * 2,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $this->post(route('dashboard.orders.cancel', $invoiceNumber))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Invoice '.$invoiceNumber.' dibatalkan oleh admin dan stok produk telah dikembalikan.');

    expect($customer->orders()->where('invoice_number', $invoiceNumber)->where('status', 'cancelled')->count())->toBe(2)
        ->and($headphones->fresh()->stock)->toBe($headphoneStock + 1)
        ->and($lamp->fresh()->stock)->toBe($lampStock + 2);

    $this->post(route('dashboard.orders.cancel', $invoiceNumber))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('error');

    expect($headphones->fresh()->stock)->toBe($headphoneStock + 1)
        ->and($lamp->fresh()->stock)->toBe($lampStock + 2);
});

test('dashboard shows completed orders without admin completion actions', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000043';
    $customer->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Pesanan Selesai')
        ->assertDontSee('Selesaikan pesanan');
});
