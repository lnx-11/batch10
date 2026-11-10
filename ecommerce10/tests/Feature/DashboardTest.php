<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::factory()->create());
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
        ->assertSee('min-w-[1000px]', false)
        ->assertSee('Data contoh pesanan — belum ada pesanan sebenarnya')
        ->assertSee('Data contoh status — status belum tersedia pada data pesanan')
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
            'user_id' => $user->id,
            'product_id' => $earlierProduct->id,
            'quantity' => 1,
            'total' => $earlierProduct->price,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        [
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
            'Kamera Pocket 35',
            'Pelanggan Terbaru',
            'Headphone Studio One',
        ])
        ->assertSee('Rp '.number_format($latestProduct->price * 2, 0, ',', '.'))
        ->assertSee('data-dashboard-clicks', false)
        ->assertDontSee('Data contoh pesanan — belum ada pesanan sebenarnya')
        ->assertDontSee('Data contoh klik — belum ada klik sebenarnya')
        ->assertDontSee('data-dashboard-order-status', false)
        ->assertSee('Status pesanan belum tersedia pada data aplikasi.');
});
