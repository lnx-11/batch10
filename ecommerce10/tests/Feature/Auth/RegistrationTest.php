<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200)
        ->assertSee('LИX.goods')
        ->assertSee('images/brand-logo.jpg')
        ->assertSee('aria-controls="password"', false)
        ->assertSee('aria-controls="password_confirmation"', false);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $this->assertSame('user', auth()->user()->fresh()->role);
    $response->assertRedirect(route('home', absolute: false));
});
