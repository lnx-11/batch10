<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $items = $user->cartItems()->with('product')->get();

        if ($items->isEmpty()) {
            return to_route('cart.index')->with('error', 'Keranjang masih kosong.');
        }

        $total = $items->sum(fn (Cart $item): int => $item->product->price * $item->quantity);

        return view('checkout.index', compact('items', 'total'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $phone = trim((string) config('services.whatsapp.phone', ''));

        if ($phone !== '' && preg_match('/\A[1-9]\d{7,14}\z/', $phone) !== 1) {
            return back()->with('error', 'Nomor WhatsApp toko belum dikonfigurasi dengan benar.');
        }

        $result = DB::transaction(function () use ($user): array {
            $cartItems = $user->cartItems()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                return ['error' => 'Keranjang masih kosong.'];
            }

            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (! $product instanceof Product) {
                    return ['error' => 'Salah satu produk di keranjang sudah tidak tersedia.'];
                }

                if ($product->stock < $cartItem->quantity) {
                    return ['error' => 'Stok '.$product->name.' tidak mencukupi. Perbarui keranjang sebelum checkout.'];
                }
            }

            $orderLines = [];
            $orderIds = [];
            $total = 0;

            foreach ($cartItems as $cartItem) {
                $product = Product::query()->findOrFail($cartItem->product_id);
                $lineTotal = $product->price * $cartItem->quantity;
                $order = Order::query()->create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $cartItem->quantity,
                    'total' => $lineTotal,
                    'status' => Order::STATUS_PENDING,
                ]);

                $product->decrement('stock', $cartItem->quantity);
                $orderIds[] = $order->id;
                $orderLines[] = [
                    'name' => $product->name,
                    'quantity' => $cartItem->quantity,
                    'price' => $product->price,
                    'total' => $lineTotal,
                ];
                $total += $lineTotal;
            }

            $invoiceNumber = sprintf('INV-%s-%08d', now()->format('Ymd'), $orderIds[0]);
            Order::query()
                ->whereIn('id', $orderIds)
                ->update(['invoice_number' => $invoiceNumber]);

            $user->cartItems()->delete();

            return [
                'invoice_number' => $invoiceNumber,
                'lines' => $orderLines,
                'total' => $total,
            ];
        });

        if (isset($result['error'])) {
            return to_route('cart.index')->with('error', $result['error']);
        }

        $messageLines = [
            'Halo, saya ingin konfirmasi pesanan.',
            'Nama: '.$user->name,
            'No. invoice: '.$result['invoice_number'],
            'Daftar produk:',
        ];

        foreach ($result['lines'] as $line) {
            $messageLines[] = sprintf(
                '- %s x%d: Rp %s',
                $line['name'],
                $line['quantity'],
                number_format($line['total'], 0, ',', '.'),
            );
        }

        $messageLines[] = 'Total: Rp '.number_format($result['total'], 0, ',', '.');
        $whatsAppUrl = 'https://wa.me/'.$phone.'?text='.rawurlencode(implode("\n", $messageLines));

        return redirect()->away($whatsAppUrl);
    }

    private function authenticatedUser(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
}
