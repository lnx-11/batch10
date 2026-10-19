<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$products = [
    ['id' => 1, 'name' => 'Sneaker Everyday', 'category' => 'Gaya hidup', 'description' => 'Ringan untuk langkah panjang dan santai.', 'price' => 579000, 'stock' => 25, 'label' => 'Terlaris', 'image' => asset('images/products/sneaker.jpg')],
    ['id' => 2, 'name' => 'Tas Harian Transit', 'category' => 'Aksesori', 'description' => 'Ruang cukup untuk semua yang penting.', 'price' => 359000, 'stock' => 30, 'label' => '', 'image' => asset('images/products/tas-transit.jpg')],
    ['id' => 3, 'name' => 'Jam Tangan Field', 'category' => 'Gaya hidup', 'description' => 'Desain sederhana, siap ikut ke mana saja.', 'price' => 789000, 'stock' => 10, 'label' => 'Baru', 'image' => asset('images/products/jam-tangan.jpg')],
    ['id' => 4, 'name' => 'Headphone Studio One', 'category' => 'Teknologi', 'description' => 'Suara detail, nyaman dipakai seharian.', 'price' => 649000, 'stock' => 15, 'label' => 'Favorit', 'image' => asset('images/products/headphone-studio.jpg')],
    ['id' => 5, 'name' => 'Lampu Meja Arc', 'category' => 'Rumah', 'description' => 'Cahaya hangat untuk sudut yang tenang.', 'price' => 429000, 'stock' => 20, 'label' => 'Pilihan editor', 'image' => asset('images/products/lampu-meja.jpg')],
    ['id' => 6, 'name' => 'Tumbler Transit', 'category' => 'Gaya hidup', 'description' => 'Menjaga minuman tetap pas sepanjang hari.', 'price' => 189000, 'stock' => 50, 'label' => '', 'image' => asset('images/products/tumbler.jpg')],
    ['id' => 7, 'name' => 'Kamera Pocket 35', 'category' => 'Teknologi', 'description' => 'Momen sehari-hari, tersimpan lebih berkesan.', 'price' => 1199000, 'stock' => 8, 'label' => 'Edisi pilihan', 'image' => asset('images/products/kamera-pocket.jpg')],
    ['id' => 8, 'name' => 'Vas Bentuk Sora', 'category' => 'Rumah', 'description' => 'Siluet organik untuk meja dan rak favorit.', 'price' => 249000, 'stock' => 12, 'label' => '', 'image' => asset('images/products/vas-sora.jpg')],
];

Route::get('/', function () use ($products) {
    return view('home.index', ['products' => array_slice($products, 0, 4)]);
});

Route::get('products', function () use ($products) {
    return view('product.description', compact('products'));
})->name('products.index');

Route::get('cart', function () use ($products) {
    $catalog = collect($products)->keyBy('id');
    $items = collect(session('cart', []))
        ->map(function (int $quantity, int $productId) use ($catalog) {
            $product = $catalog->get($productId);

            return $product ? [...$product, 'qty' => $quantity] : null;
        })
        ->filter()
        ->values()
        ->all();

    return view('cart.index', compact('items'));
})->name('cart.index');

Route::post('cart/{productId}', function (Request $request, int $productId) use ($products) {
    $product = collect($products)->firstWhere('id', $productId);
    abort_if($product === null, 404);

    $cart = $request->session()->get('cart', []);
    $cart[$productId] = min(($cart[$productId] ?? 0) + 1, 99);
    $request->session()->put('cart', $cart);
    $message = $product['name'].' ditambahkan ke keranjang.';

    if ($request->expectsJson()) {
        return response()->json([
            'message' => $message,
            'cartCount' => array_sum($cart),
        ]);
    }

    return back()->with('success', $message);
})->name('cart.store');

Route::post('cart/{productId}/quantity', function (Request $request, int $productId) use ($products) {
    abort_if(collect($products)->firstWhere('id', $productId) === null, 404);
    $validated = $request->validate(['delta' => ['required', 'integer', 'in:-1,1']]);

    $cart = $request->session()->get('cart', []);
    $quantity = ($cart[$productId] ?? 0) + $validated['delta'];

    if ($quantity < 1) {
        unset($cart[$productId]);
        $message = 'Produk dihapus dari keranjang.';
    } else {
        $cart[$productId] = $quantity;
        $message = 'Jumlah produk diperbarui.';
    }

    $request->session()->put('cart', $cart);

    return back()->with('success', $message);
})->name('cart.quantity');

Route::post('cart/{productId}/remove', function (Request $request, int $productId) {
    $cart = $request->session()->get('cart', []);
    $cart = array_filter($cart, static fn ($key) => (int) $key !== $productId, ARRAY_FILTER_USE_KEY);
    $request->session()->put('cart', $cart);

    return back()->with('success', 'Produk dihapus dari keranjang.');
})->name('cart.remove');

Route::get('checkout', function () {
    return response('Checkout page');
});