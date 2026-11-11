<?php

use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

test('guests must log in to view purchase history', function () {
    $this->get(route('purchases.index'))
        ->assertRedirect(route('login'));
});

test('a user sees only their own purchase history', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $anotherProduct = Product::where('slug', 'kamera-pocket-35')->firstOrFail();

    $user->orders()->create([
        'invoice_number' => 'INV-20261111-00000001',
        'product_id' => $product->id,
        'quantity' => 2,
        'total' => $product->price * 2,
    ]);
    $anotherUser->orders()->create([
        'invoice_number' => 'INV-20261111-00000002',
        'product_id' => $anotherProduct->id,
        'quantity' => 1,
        'total' => $anotherProduct->price,
    ]);

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertOk()
        ->assertSee('Riwayat pembelian.')
        ->assertSee('INV-20261111-00000001')
        ->assertSee('2')
        ->assertSee('Rp '.number_format($product->price * 2, 0, ',', '.'))
        ->assertDontSee('Kamera Pocket 35')
        ->assertDontSee('INV-20261111-00000002')
        ->assertSee(route('purchases.show', 'INV-20261111-00000001'), false);

    $this->get(route('purchases.show', 'INV-20261111-00000001'))
        ->assertOk()
        ->assertSee('Headphone Studio One');
});

test('purchase history uses consistent colors for every order status', function () {
    $user = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $statuses = [
        ['INV-20261111-00000021', 'pending'],
        ['INV-20261111-00000022', 'processing'],
        ['INV-20261111-00000023', 'completed'],
        ['INV-20261111-00000024', 'cancelled'],
    ];

    foreach ($statuses as [$invoiceNumber, $status]) {
        $user->orders()->create([
            'invoice_number' => $invoiceNumber,
            'product_id' => $product->id,
            'quantity' => 1,
            'total' => $product->price,
            'status' => $status,
        ]);
    }

    $history = $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertOk();

    foreach ($statuses as [$invoiceNumber, $status]) {
        $history->assertSee('order-status-badge order-status-'.$status, false);
    }

    $this->get(route('purchases.show', 'INV-20261111-00000024'))
        ->assertOk()
        ->assertSee('order-status-badge order-status-cancelled', false);
});

test('one invoice groups all products from a checkout and shows its details', function () {
    $user = User::factory()->create();
    config(['services.whatsapp.phone' => '6285152195546']);
    $headphones = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $lamp = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000010';

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

    $this->actingAs($user)
        ->get(route('purchases.index'))
        ->assertOk()
        ->assertSee($invoiceNumber)
        ->assertSee('Rp '.number_format($headphones->price + $lamp->price * 2, 0, ',', '.'))
        ->assertSee('2', false);

    $this->get(route('purchases.show', $invoiceNumber))
        ->assertOk()
        ->assertSee($invoiceNumber)
        ->assertSee('Headphone Studio One')
        ->assertSee('Lampu Meja Arc')
        ->assertSee('Belum diverifikasi')
        ->assertSee('Konfirmasi via WhatsApp')
        ->assertSee('wa.me/6285152195546', false);
});

test('a user cannot view another users invoice', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000011';

    $owner->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
    ]);

    $this->actingAs($otherUser)
        ->get(route('purchases.show', $invoiceNumber))
        ->assertNotFound();
});

test('a user can cancel a pending invoice and restore stock for every item only once', function () {
    $user = User::factory()->create();
    $headphones = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $lamp = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000012';
    $headphoneStock = $headphones->stock;
    $lampStock = $lamp->stock;

    $headphones->decrement('stock', 1);
    $lamp->decrement('stock', 2);
    $user->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $headphones->id,
        'quantity' => 1,
        'total' => $headphones->price,
        'status' => 'pending',
    ]);
    $user->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $lamp->id,
        'quantity' => 2,
        'total' => $lamp->price * 2,
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->post(route('purchases.cancel', $invoiceNumber))
        ->assertRedirect(route('purchases.show', $invoiceNumber))
        ->assertSessionHas('success', 'Pesanan berhasil dibatalkan dan stok produk telah dikembalikan.');

    $this->assertDatabaseHas('orders', [
        'invoice_number' => $invoiceNumber,
        'status' => 'cancelled',
    ]);
    expect($headphones->fresh()->stock)->toBe($headphoneStock)
        ->and($lamp->fresh()->stock)->toBe($lampStock);

    $this->post(route('purchases.cancel', $invoiceNumber))
        ->assertRedirect(route('purchases.show', $invoiceNumber))
        ->assertSessionHas('error', 'Pesanan hanya bisa dibatalkan saat statusnya masih Pending.');

    expect($headphones->fresh()->stock)->toBe($headphoneStock)
        ->and($lamp->fresh()->stock)->toBe($lampStock);

    $this->get(route('purchases.show', $invoiceNumber))
        ->assertOk()
        ->assertSee('Cancelled')
        ->assertSee('Pesanan ini sudah dibatalkan dan stok telah dikembalikan.')
        ->assertDontSee('Batalkan pesanan');
});

test('a user cannot cancel an invoice after it has started processing', function () {
    $user = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000013';
    $stock = $product->stock;

    $user->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        'status' => 'processing',
    ]);

    $this->actingAs($user)
        ->post(route('purchases.cancel', $invoiceNumber))
        ->assertRedirect(route('purchases.show', $invoiceNumber))
        ->assertSessionHas('error', 'Pesanan hanya bisa dibatalkan saat statusnya masih Pending.');

    $this->assertDatabaseHas('orders', [
        'invoice_number' => $invoiceNumber,
        'status' => 'processing',
    ]);
    expect($product->fresh()->stock)->toBe($stock);
});

test('a customer can confirm receipt of every item in a verified processing invoice', function () {
    $user = User::factory()->create();
    $headphones = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $lamp = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000014';

    foreach ([[$headphones, 1], [$lamp, 2]] as [$product, $quantity]) {
        $user->orders()->create([
            'invoice_number' => $invoiceNumber,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'total' => $product->price * $quantity,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);
    }

    $this->actingAs($user)
        ->get(route('purchases.show', $invoiceNumber))
        ->assertOk()
        ->assertSee(route('purchases.complete', $invoiceNumber), false)
        ->assertSee('Konfirmasi pesanan diterima');

    $this->post(route('purchases.complete', $invoiceNumber))
        ->assertRedirect(route('purchases.show', $invoiceNumber))
        ->assertSessionHas('success', 'Penerimaan pesanan berhasil dikonfirmasi. Status pesanan menjadi Completed.');

    expect($user->orders()->where('invoice_number', $invoiceNumber)->where('status', 'completed')->count())->toBe(2);

    $this->get(route('purchases.show', $invoiceNumber))
        ->assertOk()
        ->assertSee('Penerimaan pesanan ini sudah kamu konfirmasi.')
        ->assertDontSee('Konfirmasi pesanan diterima');
});

test('a customer cannot confirm receipt for another user or an unverified order', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $invoiceNumber = 'INV-20261111-00000015';
    $owner->orders()->create([
        'invoice_number' => $invoiceNumber,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        'status' => 'processing',
        'payment_status' => 'paid',
    ]);

    $this->actingAs($otherUser)
        ->post(route('purchases.complete', $invoiceNumber))
        ->assertNotFound();

    $pendingInvoice = 'INV-20261111-00000016';
    $owner->orders()->create([
        'invoice_number' => $pendingInvoice,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);

    $this->actingAs($owner)
        ->post(route('purchases.complete', $pendingInvoice))
        ->assertRedirect(route('purchases.show', $pendingInvoice))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('orders', [
        'invoice_number' => $pendingInvoice,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);
});

test('purchase history shows a clear empty state', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('purchases.index'))
        ->assertOk()
        ->assertSee('Belum ada pembelian')
        ->assertSee('Pesananmu akan terlihat di sini setelah checkout.');
});

test('profile shows account details and links to the purchase history', function () {
    $user = User::factory()->create([
        'name' => 'Pelanggan Contoh',
        'email' => 'pelanggan@example.com',
        'role' => 'user',
    ]);
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $user->orders()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Ringkasan akun')
        ->assertSee('Pelanggan Contoh')
        ->assertSee('pelanggan@example.com')
        ->assertSee('User')
        ->assertSee('Item pesanan')
        ->assertSee('>1</strong>', false)
        ->assertSee(route('purchases.index'), false);
});

test('store navigation links customers to account pages and reserves dashboard for admins', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('purchases.index'), false)
        ->assertSee(route('profile.edit'), false)
        ->assertDontSee('href="'.route('dashboard').'"', false);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('dashboard'), false);
});
