<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('the shopping cart starts empty', function () {
    $this->actingAs(User::factory()->create())
        ->get('/cart')
        ->assertOk()
        ->assertSee('Keranjangmu masih kosong');
});

test('guests must log in before using the cart or checking out', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->get(route('cart.index'))
        ->assertRedirect(route('login'));

    $this->post(route('cart.store', $product))
        ->assertRedirect(route('login'));

    $this->post(route('cart.quantity', $product), ['delta' => 1])
        ->assertRedirect(route('login'));

    $this->post(route('cart.remove', $product))
        ->assertRedirect(route('login'));

    $this->get('/checkout')
        ->assertRedirect(route('login'));
});

test('the store homepage exposes Breeze login and registration to guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('Login')
        ->assertSee('Register')
        ->assertSee('Tambah')
        ->assertSee(route('login'), false)
        ->assertSee('<a href="'.route('login').'" class="btn btn-brand py-2 px-3">Tambah <span aria-hidden="true">+</span></a>', false)
        ->assertSee(route('register'), false)
        ->assertSee('href="'.route('login').'"', false)
        ->assertDontSee('href="'.route('login').'" class="btn btn-outline-brand"', false)
        ->assertSee('href="'.route('login').'" class="btn btn-brand"', false);
});

test('a product can be added from the current page without redirecting to the cart', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->post(route('cart.store', $product), [], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('cartCount', 1);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('data-cart-count>1</span>', false);
});

test('each signed-in user sees only their own saved cart items', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    $this->actingAs($firstUser)->post(route('cart.store', $product));
    $this->flushSession();
    $this->actingAs($secondUser)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Keranjangmu masih kosong')
        ->assertDontSee('Headphone Studio One');

    $this->assertDatabaseHas('carts', [
        'user_id' => $firstUser->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
});

test('a user cannot remove another users cart item', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($owner)->post(route('cart.store', $product));
    $this->actingAs($otherUser)
        ->post(route('cart.remove', $product))
        ->assertSessionHas('error', 'Produk tidak ditemukan di keranjang.');

    $this->assertDatabaseHas('carts', [
        'user_id' => $owner->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
});

test('removing one product leaves the other cart products untouched', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $sneaker = Product::where('slug', 'sneaker-everyday')->firstOrFail();
    $backpack = Product::where('slug', 'tas-harian-transit')->firstOrFail();
    $this->post(route('cart.store', $sneaker));
    $this->post(route('cart.store', $backpack));

    $this->post(route('cart.remove', $sneaker));

    $this->get('/cart')
        ->assertOk()
        ->assertSee('Tas Harian Transit')
        ->assertDontSee('Sneaker Everyday')
        ->assertSee('Rp 359.000');

    $this->assertDatabaseMissing('carts', [
        'user_id' => $user->id,
        'product_id' => $sneaker->id,
    ]);
    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $backpack->id,
        'quantity' => 1,
    ]);
});

test('cart item quantity can be increased and decreased independently', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $sneaker = Product::where('slug', 'sneaker-everyday')->firstOrFail();
    $backpack = Product::where('slug', 'tas-harian-transit')->firstOrFail();
    $this->post(route('cart.store', $sneaker));
    $this->post(route('cart.store', $backpack));
    $this->post(route('cart.quantity', $sneaker), ['delta' => 1]);

    $this->get('/cart')
        ->assertSee('Sneaker Everyday')
        ->assertSee('Rp 1.158.000')
        ->assertSee('class="text-secondary text-nowrap"', false)
        ->assertSee('class="text-end fw-bold text-nowrap"', false)
        ->assertSee('Tas Harian Transit');
    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $sneaker->id,
        'quantity' => 2,
    ]);

    $this->post(route('cart.quantity', $sneaker), ['delta' => -1]);

    $this->get('/cart')
        ->assertSee('Sneaker Everyday')
        ->assertSee('Rp 579.000')
        ->assertSee('Tas Harian Transit');
    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $sneaker->id,
        'quantity' => 1,
    ]);
});

test('checkout shows the signed-in user cart total', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->actingAs(User::factory()->create())
        ->post(route('cart.store', $product));

    $this->get(route('checkout.index'))
        ->assertOk()
        ->assertSee('Periksa pesananmu.')
        ->assertSee('Alamat pengiriman')
        ->assertSee('name="recipient_name"', false)
        ->assertSee('name="recipient_phone"', false)
        ->assertSee('name="shipping_address"', false)
        ->assertDontSee('name="shipping_city"', false)
        ->assertSee('Kota Jakarta', false)
        ->assertDontSee('Pekalongan')
        ->assertSee('Konfirmasi pesanan via WhatsApp')
        ->assertSee('class="btn btn-whatsapp w-100"', false)
        ->assertSee('class="whatsapp-icon"', false)
        ->assertSee('class="text-secondary text-nowrap"', false)
        ->assertSee('Rp '.number_format($product->price, 0, ',', '.'));
});

test('checkout creates orders reduces stock clears the cart and opens WhatsApp', function () {
    $user = User::factory()->create(['name' => 'Pelanggan Checkout']);
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $anotherProduct = Product::where('slug', 'lampu-meja-arc')->firstOrFail();
    $shippingDetails = [
        'recipient_name' => 'Penerima Checkout',
        'recipient_phone' => '081234567890',
        'shipping_address' => 'Buaran, Kabupaten Pekalongan, Jawa Tengah, 51111',
    ];
    $initialStock = $product->stock;
    $anotherInitialStock = $anotherProduct->stock;
    config(['services.whatsapp.phone' => '6281234567890']);

    $this->actingAs($user)->post(route('cart.store', $product));
    $this->post(route('cart.store', $anotherProduct));
    $response = $this->post(route('checkout.store'), $shippingDetails);

    $response->assertRedirect();
    $invoiceNumber = Order::query()
        ->where('user_id', $user->id)
        ->value('invoice_number');
    expect($response->headers->get('Location'))
        ->toContain('https://wa.me/6281234567890?text=')
        ->toContain(rawurlencode('No. invoice: '.$invoiceNumber))
        ->toContain(rawurlencode('Daftar produk:'))
        ->toContain(rawurlencode('Nama penerima: '.$shippingDetails['recipient_name']))
        ->toContain(rawurlencode('Alamat pengiriman: '.$shippingDetails['shipping_address']))
        ->toContain(rawurlencode('Lampu Meja Arc'));
    expect($invoiceNumber)->toMatch('/\AINV-\d{8}-\d{8}\z/');
    $this->assertDatabaseCount('orders', 2);
    $this->assertDatabaseHas('orders', [
        'invoice_number' => $invoiceNumber,
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        ...$shippingDetails,
    ]);
    $this->assertDatabaseHas('orders', [
        'invoice_number' => $invoiceNumber,
        'user_id' => $user->id,
        'product_id' => $anotherProduct->id,
        'quantity' => 1,
        'total' => $anotherProduct->price,
        'status' => 'pending',
        ...$shippingDetails,
    ]);
    Order::query()
        ->where('invoice_number', $invoiceNumber)
        ->update([
            'shipping_city' => 'Pekalongan',
            'shipping_province' => 'Jawa Tengah',
            'shipping_postal_code' => '51111',
        ]);
    $this->get(route('purchases.show', $invoiceNumber))
        ->assertOk()
        ->assertSee($shippingDetails['recipient_name'])
        ->assertSee($shippingDetails['recipient_phone'])
        ->assertSee($shippingDetails['shipping_address'])
        ->assertDontSee('Kabupaten Pekalongan, Pekalongan')
        ->assertSee('Buaran, Kabupaten Pekalongan, Jawa Tengah, 51111');
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard.orders.index'))
        ->assertOk()
        ->assertSee($shippingDetails['recipient_name'])
        ->assertSee($shippingDetails['recipient_phone'])
        ->assertSee('Buaran, Kabupaten Pekalongan, Jawa Tengah, 51111')
        ->assertDontSee('Kabupaten Pekalongan, Pekalongan');
    $this->assertDatabaseMissing('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
    expect($product->fresh()->stock)->toBe($initialStock - 1);
    expect($anotherProduct->fresh()->stock)->toBe($anotherInitialStock - 1);
});

test('checkout opens WhatsApp without a configured store number', function () {
    $user = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $shippingDetails = [
        'recipient_name' => 'Penerima',
        'recipient_phone' => '081234567890',
        'shipping_address' => 'Jalan Melati No. 1, Bandung, Jawa Barat',
    ];
    config(['services.whatsapp.phone' => null]);

    $this->actingAs($user)->post(route('cart.store', $product));
    $response = $this->post(route('checkout.store'), $shippingDetails);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->toContain('https://wa.me/?text=');
});

test('checkout rejects a malformed configured WhatsApp number without creating an order', function () {
    $user = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $shippingDetails = [
        'recipient_name' => 'Penerima',
        'recipient_phone' => '081234567890',
        'shipping_address' => 'Jalan Melati No. 1, Bandung, Jawa Barat',
    ];
    config(['services.whatsapp.phone' => 'not-a-phone-number']);

    $this->actingAs($user)->post(route('cart.store', $product));
    $this->from(route('checkout.index'))
        ->post(route('checkout.store'), $shippingDetails)
        ->assertRedirect(route('checkout.index'))
        ->assertSessionHas('error', 'Nomor WhatsApp toko belum dikonfigurasi dengan benar.');

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

test('checkout keeps the cart when product stock is no longer sufficient', function () {
    $user = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();
    $shippingDetails = [
        'recipient_name' => 'Penerima',
        'recipient_phone' => '081234567890',
        'shipping_address' => 'Jalan Melati No. 1, Bandung, Jawa Barat',
    ];

    $this->actingAs($user)->post(route('cart.store', $product));
    $product->update(['stock' => 0]);

    $this->from(route('checkout.index'))
        ->post(route('checkout.store'), $shippingDetails)
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error', 'Stok Headphone Studio One tidak mencukupi. Perbarui keranjang sebelum checkout.');

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

test('checkout requires a complete shipping address and keeps the cart when it is invalid', function () {
    $user = User::factory()->create();
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->actingAs($user)->post(route('cart.store', $product));
    $this->from(route('checkout.index'))
        ->post(route('checkout.store'), [
            'recipient_name' => 'Penerima',
            'recipient_phone' => '081234567890',
        ])
        ->assertRedirect(route('checkout.index'))
        ->assertSessionHasErrors([
            'shipping_address',
        ]);

    $this->assertDatabaseHas('carts', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'quantity' => 1,
    ]);
    $this->assertDatabaseMissing('orders', [
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);
});

test('the catalog loads products from the database and paginates six products per page', function () {
    $this->get('/products')
        ->assertOk()
        ->assertSee('Kategori produk')
        ->assertSee('Teknologi')
        ->assertSee('Rumah')
        ->assertSee('Headphone Studio One')
        ->assertSee('Kamera Pocket 35')
        ->assertDontSee('Vas Bentuk Sora')
        ->assertSee('1', false)
        ->assertSee('2', false);

    $this->get('/products?page=2')
        ->assertOk()
        ->assertSee('Vas Bentuk Sora')
        ->assertSee('Tas Harian Transit')
        ->assertDontSee('Headphone Studio One');
});

test('the catalog filters products by name or description', function () {
    $this->get(route('products.index', ['search' => 'Headphone Studio One']))
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertDontSee('Kamera Pocket 35')
        ->assertSee('name="search"', false)
        ->assertSee('value="Headphone Studio One"', false);
});

test('catalog categories are clickable filters and can be combined with search', function () {
    $technology = ProductCategory::where('slug', 'teknologi')->firstOrFail();

    $this->get(route('products.index', ['category_id' => $technology->id]))
        ->assertOk()
        ->assertSee(route('products.index', ['category_id' => $technology->id]), false)
        ->assertSee('name="category_id"', false)
        ->assertSee('aria-current="true"', false)
        ->assertSee('Headphone Studio One')
        ->assertDontSee('Vas Bentuk Sora');

    $this->get(route('products.index', [
        'category_id' => $technology->id,
        'search' => 'Headphone',
    ]))
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertDontSee('Kamera Pocket 35')
        ->assertSee('value="Headphone"', false);
});
