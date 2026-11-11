<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderHistoryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman yang dapat dibuka tanpa login
|--------------------------------------------------------------------------
| Setiap route menghubungkan URL dan method HTTP ke method controller.
| name() memberi nama route agar URL bisa dipanggil dengan route('nama.route')
| dari Blade atau controller tanpa menulis alamat URL secara manual.
*/
Route::get('/', [ProductController::class, 'home'])->name('home');
Route::get('/shop', [ProductController::class, 'home'])->name('shop.home');
Route::get('/products', [ProductController::class, 'catalog'])->name('products.index');

// Mencatat klik pada produk; throttle membatasi maksimal 120 request per menit.
Route::post('/products/{product}/click', [ProductController::class, 'trackClick'])
    ->middleware('throttle:120,1')
    ->name('products.click');

/*
|--------------------------------------------------------------------------
| Route form produk dan kategori untuk admin
|--------------------------------------------------------------------------
| Kedua middleware berlaku untuk semua route di dalam group:
| - auth: pengguna harus login.
| - admin: pengguna harus memiliki hak akses admin.
|
| create menampilkan form (GET), store menerima form (POST), dan update
| mengubah data yang sudah ada (PUT). Parameter dalam {kurung} diisi dari URL.
*/
Route::middleware(['auth', 'admin'])->group(function () {
    // Form tambah produk dan proses penyimpanan produk baru.
    Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
    Route::post('/product', [ProductController::class, 'store'])->name('product.store');

    // Memperbarui produk tertentu; {product} dicocokkan dengan model Product.
    Route::put('/product/{product}', [ProductController::class, 'update'])->name('product.update');

    // Form tambah kategori dan proses penyimpanan kategori baru.
    Route::get('/product-category/create', [ProductCategoryController::class, 'create'])->name('product-category.create');
    Route::post('/product-category', [ProductCategoryController::class, 'store'])->name('product-category.store');

    // Memperbarui kategori tertentu; {productCategory} dicocokkan dengan model ProductCategory.
    Route::put('/product-category/{productCategory}', [ProductCategoryController::class, 'update'])->name('product-category.update');
});

/*
|--------------------------------------------------------------------------
| Dashboard dan pengelolaan katalog admin
|--------------------------------------------------------------------------
| prefix('dashboard') menambahkan /dashboard di depan setiap URL dalam group.
| Route::resource membuat route CRUD standar Laravel. except('show') berarti
| route untuk melihat satu data secara terpisah tidak dibuat.
| names() memberi awalan nama, contohnya dashboard.products.index.
*/
Route::prefix('dashboard')->middleware(['auth', 'admin'])->group(function () {
    // Halaman utama dashboard.
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/orders/{invoiceNumber}/accept', [DashboardController::class, 'acceptOrder'])
        ->where('invoiceNumber', 'INV-[0-9]{8}-[0-9]{8}')
        ->name('dashboard.orders.accept');
    Route::post('/orders/{invoiceNumber}/cancel', [DashboardController::class, 'cancelOrder'])
        ->where('invoiceNumber', 'INV-[0-9]{8}-[0-9]{8}')
        ->name('dashboard.orders.cancel');
    // Route CRUD produk dan kategori, kecuali halaman detail (show).
    Route::resource('products', ProductController::class)
        ->except('show')
        ->names('dashboard.products');
    Route::resource('product-categories', ProductCategoryController::class)
        ->except('show')
        ->names('dashboard.product-categories');
});

/*
|--------------------------------------------------------------------------
| Fitur yang membutuhkan login
|--------------------------------------------------------------------------
| auth memastikan pengunjung login sebelum mengakses keranjang dan profil.
| Route keranjang memakai POST untuk mengubah data, sedangkan profil memakai
| PATCH untuk pembaruan sebagian data dan DELETE untuk menghapus akun.
*/
Route::middleware('auth')->group(function () {
    Route::get('/riwayat-pembelian', [OrderHistoryController::class, 'index'])->name('purchases.index');
    Route::get('/riwayat-pembelian/{invoiceNumber}', [OrderHistoryController::class, 'show'])
        ->where('invoiceNumber', 'INV-[0-9]{8}-[0-9]{8}')
        ->name('purchases.show');
    Route::post('/riwayat-pembelian/{invoiceNumber}/cancel', [OrderHistoryController::class, 'cancel'])
        ->where('invoiceNumber', 'INV-[0-9]{8}-[0-9]{8}')
        ->name('purchases.cancel');
    Route::post('/riwayat-pembelian/{invoiceNumber}/complete', [OrderHistoryController::class, 'complete'])
        ->where('invoiceNumber', 'INV-[0-9]{8}-[0-9]{8}')
        ->name('purchases.complete');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/{product}/quantity', [CartController::class, 'updateQuantity'])->name('cart.quantity');
    Route::post('/cart/{product}/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Memuat route login, register, logout, dan autentikasi lainnya.
require __DIR__.'/auth.php';
