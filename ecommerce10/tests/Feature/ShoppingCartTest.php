<?php

use App\Models\Product;
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
        ->assertSee('href="'.route('login').'" class="btn btn-outline-brand"', false)
        ->assertSee('href="'.route('login').'" class="btn btn-brand"', false);
});

test('a product can be added from the current page without redirecting to the cart', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->actingAs(User::factory()->create())
        ->from('/')
        ->post(route('cart.store', $product), [], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('cartCount', 1);

    $this->get('/')
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('data-cart-count>1</span>', false);
});

test('removing one product leaves the other cart products untouched', function () {
    $this->actingAs(User::factory()->create());
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
    $this->actingAs(User::factory()->create());
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
