<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $user = $this->authenticatedUser($request);
        $items = $user->cartItems()
            ->with('product')
            ->get()
            ->map(fn (Cart $item): array => [
                'id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'image' => $item->product->image,
                'stock' => $item->product->stock,
                'qty' => $item->quantity,
            ])
            ->all();

        return view('cart.index', compact('items'));
    }

    public function store(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $message = DB::transaction(function () use ($user, $product): ?string {
            $currentProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            abort_if($currentProduct->stock < 1, 404, 'Produk sedang habis.');

            $cartItem = $user->cartItems()
                ->where('product_id', $currentProduct->id)
                ->lockForUpdate()
                ->first();
            $currentQuantity = $cartItem?->quantity ?? 0;
            $maximumQuantity = min($currentProduct->stock, 99);

            if ($currentQuantity >= $maximumQuantity) {
                return 'Jumlah produk sudah mencapai stok yang tersedia.';
            }

            if ($cartItem === null) {
                $user->cartItems()->create([
                    'product_id' => $currentProduct->id,
                    'quantity' => 1,
                ]);
            } else {
                $cartItem->increment('quantity');
            }

            return null;
        });

        if ($message !== null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $message = $product->name.' ditambahkan ke keranjang.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'cartCount' => $user->cartItems()->sum('quantity'),
            ]);
        }

        return back()->with('success', $message);
    }

    public function updateQuantity(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate(['delta' => ['required', 'integer', 'in:-1,1']]);
        $user = $this->authenticatedUser($request);
        $result = DB::transaction(function () use ($user, $product, $validated): array {
            $currentProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $cartItem = $user->cartItems()
                ->where('product_id', $currentProduct->id)
                ->lockForUpdate()
                ->first();

            if ($cartItem === null) {
                return ['status' => 'error', 'message' => 'Produk tidak ditemukan di keranjang.'];
            }

            $quantity = $cartItem->quantity + $validated['delta'];

            if ($quantity < 1) {
                $cartItem->delete();

                return ['status' => 'success', 'message' => 'Produk dihapus dari keranjang.'];
            }

            if ($quantity > min($currentProduct->stock, 99)) {
                return ['status' => 'error', 'message' => 'Jumlah produk sudah mencapai stok yang tersedia.'];
            }

            $cartItem->update(['quantity' => $quantity]);

            return ['status' => 'success', 'message' => 'Jumlah produk diperbarui.'];
        });

        return back()->with($result['status'], $result['message']);
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $deleted = $user->cartItems()->where('product_id', $product->id)->delete();

        return back()->with(
            $deleted > 0 ? 'success' : 'error',
            $deleted > 0 ? 'Produk dihapus dari keranjang.' : 'Produk tidak ditemukan di keranjang.',
        );
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
