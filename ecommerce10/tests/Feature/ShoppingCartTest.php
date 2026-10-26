<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('the shopping cart starts empty', function () {
    $this->get('/cart')
        ->assertOk()
        ->assertSee('Keranjangmu masih kosong');
});

test('a product can be added from the current page without redirecting to the cart', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->from('/')->post(route('cart.store', $product), [], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('cartCount', 1);

    $this->get('/')
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('data-cart-count>1</span>', false);
});

test('removing one product leaves the other cart products untouched', function () {
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
});

test('cart item quantity can be increased and decreased independently', function () {
    $sneaker = Product::where('slug', 'sneaker-everyday')->firstOrFail();
    $backpack = Product::where('slug', 'tas-harian-transit')->firstOrFail();
    $this->post(route('cart.store', $sneaker));
    $this->post(route('cart.store', $backpack));
    $this->post(route('cart.quantity', $sneaker), ['delta' => 1]);

    $this->get('/cart')
        ->assertSee('Sneaker Everyday')
        ->assertSee('Rp 1.158.000')
        ->assertSee('Tas Harian Transit');

    $this->post(route('cart.quantity', $sneaker), ['delta' => -1]);

    $this->get('/cart')
        ->assertSee('Sneaker Everyday')
        ->assertSee('Rp 579.000')
        ->assertSee('Tas Harian Transit');
});

test('the catalog loads products from the database and paginates six products per page', function () {
    $this->get('/products')
        ->assertOk()
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
