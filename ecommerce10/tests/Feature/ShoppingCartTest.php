<?php

test('the shopping cart starts empty', function () {
    $this->get('/cart')
        ->assertOk()
        ->assertSee('Keranjangmu masih kosong');
});

test('a product can be added from the current page without redirecting to the cart', function () {
    $this->from('/')->post('/cart/1', [], ['Accept' => 'application/json'])
        ->assertOk()
        ->assertJsonPath('cartCount', 1);

    $this->get('/')
        ->assertOk()
        ->assertSee('Sneaker Everyday')
        ->assertSee('data-cart-count>1</span>', false);
});

test('removing one product leaves the other cart products untouched', function () {
    $this->post('/cart/1');
    $this->post('/cart/2');

    $this->post('/cart/1/remove');

    $this->get('/cart')
        ->assertOk()
        ->assertSee('Tas Harian Transit')
        ->assertDontSee('Sneaker Everyday')
        ->assertSee('Rp 359.000');
});

test('cart item quantity can be increased and decreased independently', function () {
    $this->post('/cart/1');
    $this->post('/cart/2');
    $this->post('/cart/1/quantity', ['delta' => 1]);

    $this->get('/cart')
        ->assertSee('Sneaker Everyday')
        ->assertSee('Rp 1.158.000')
        ->assertSee('Tas Harian Transit');

    $this->post('/cart/1/quantity', ['delta' => -1]);

    $this->get('/cart')
        ->assertSee('Sneaker Everyday')
        ->assertSee('Rp 579.000')
        ->assertSee('Tas Harian Transit');
});
test('the catalog contains the eight products from the previous assignment', function () {
    $this->get('/products')
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('Lampu Meja Arc')
        ->assertSee('Jam Tangan Field')
        ->assertSee('Sneaker Everyday')
        ->assertSee('Tumbler Transit')
        ->assertSee('Kamera Pocket 35')
        ->assertSee('Vas Bentuk Sora')
        ->assertSee('Tas Harian Transit');
});