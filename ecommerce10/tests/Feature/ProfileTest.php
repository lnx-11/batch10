<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk()
        ->assertSee('Profil saya.')
        ->assertSee('Informasi pribadi')
        ->assertSee('Ubah kata sandi')
        ->assertSee('Hapus akun')
        ->assertSee('class="navbar navbar-expand-lg site-nav', false)
        ->assertDontSee('class="admin-navigation"', false)
        ->assertSee('name="current_password"', false);
});

test('admin profile continues to use the admin layout', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/profile')
        ->assertOk()
        ->assertSee('class="admin-navigation"', false)
        ->assertDontSee('Profil saya.');
});

test('dashboard resource routes are protected and use the dashboard prefix', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->get(route('dashboard.products.index'))
        ->assertRedirect(route('login'));

    $this->get(route('dashboard.product-categories.index'))
        ->assertRedirect(route('login'));

    $this->assertSame('/dashboard/products', route('dashboard.products.index', absolute: false));
    $this->assertSame('/dashboard/product-categories', route('dashboard.product-categories.index', absolute: false));

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
