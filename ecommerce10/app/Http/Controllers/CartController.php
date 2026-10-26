<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(): View
    {
        $quantities = session('cart', []);
        $products = Product::query()
            ->whereIn('id', array_keys($quantities))
            ->get()
            ->keyBy('id');

        $items = collect($quantities)
            ->map(function (int $quantity, int $productId) use ($products) {
                $product = $products->get($productId);

                return $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'qty' => $quantity,
                ] : null;
            })
            ->filter()
            ->values()
            ->all();

        return view('cart.index', compact('items'));
    }

    public function store(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        abort_if($product->stock < 1, 404, 'Produk sedang habis.');

        $cart = $request->session()->get('cart', []);
        $currentQuantity = $cart[$product->id] ?? 0;

        if ($currentQuantity >= min($product->stock, 99)) {
            $message = 'Jumlah produk sudah mencapai stok yang tersedia.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $cart[$product->id] = $currentQuantity + 1;
        $request->session()->put('cart', $cart);
        $message = $product->name.' ditambahkan ke keranjang.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cartCount' => array_sum($cart),
            ]);
        }

        return back()->with('success', $message);
    }

    public function updateQuantity(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['delta' => ['required', 'integer', 'in:-1,1']]);
        $cart = $request->session()->get('cart', []);
        $quantity = ($cart[$product->id] ?? 0) + $validated['delta'];

        if ($quantity < 1) {
            unset($cart[$product->id]);
            $message = 'Produk dihapus dari keranjang.';
        } elseif ($quantity > min($product->stock, 99)) {
            $message = 'Jumlah produk sudah mencapai stok yang tersedia.';
        } else {
            $cart[$product->id] = $quantity;
            $message = 'Jumlah produk diperbarui.';
        }

        $request->session()->put('cart', $cart);

        return back()->with('success', $message);
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Produk dihapus dari keranjang.');
    }
}
