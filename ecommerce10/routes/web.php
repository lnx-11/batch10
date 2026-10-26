<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/shop', [ProductController::class, 'home'])->name('shop.home');
Route::get('/products', [ProductController::class, 'catalog'])->name('products.index');

Route::prefix('dashboard')->middleware('auth')->group(function () {
    Route::view('/', 'dashboard')->name('dashboard');
    Route::resource('products', ProductController::class)
        ->except('show')
        ->names('dashboard.products');
    Route::resource('product-categories', ProductCategoryController::class)
        ->except('show')
        ->names('dashboard.product-categories');
});

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/{product}/quantity', [CartController::class, 'updateQuantity'])->name('cart.quantity');
    Route::post('/cart/{product}/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', fn () => response('Checkout page'));

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
