<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $invoices = $user->orders()
            ->select('invoice_number')
            ->selectRaw('MAX(created_at) as created_at')
            ->selectRaw('SUM(total) as total')
            ->selectRaw('COUNT(*) as item_count')
            ->selectRaw('MAX(status) as status')
            ->selectRaw('MAX(payment_status) as payment_status')
            ->groupBy('invoice_number')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('purchases.index', compact('invoices'));
    }

    public function show(Request $request, string $invoiceNumber): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $orders = $user->orders()
            ->where('invoice_number', $invoiceNumber)
            ->with(['product', 'paymentVerifier'])
            ->orderBy('id')
            ->get();

        abort_if($orders->isEmpty(), 404);

        $total = $orders->sum('total');
        $status = $orders->first()->status;
        $paymentStatus = $orders->first()->payment_status;
        $paymentVerifiedAt = $orders->first()->payment_verified_at;
        $paymentVerifier = $orders->first()->paymentVerifier;
        $canConfirmReceipt = $orders->every(fn (Order $order): bool => $order->status === Order::STATUS_PROCESSING
            && $order->payment_status === Order::PAYMENT_PAID);
        $messageLines = [
            'Halo, saya ingin konfirmasi pesanan.',
            'Nama: '.$user->name,
            'No. invoice: '.$invoiceNumber,
            'Daftar produk:',
        ];

        foreach ($orders as $order) {
            $messageLines[] = sprintf(
                '- %s x%d: Rp %s',
                $order->product?->name ?? 'Produk tidak tersedia',
                $order->quantity,
                number_format($order->total, 0, ',', '.'),
            );
        }

        $messageLines[] = 'Total: Rp '.number_format($total, 0, ',', '.');
        $phone = trim((string) config('services.whatsapp.phone', ''));
        $whatsAppUrl = 'https://wa.me/'.$phone.'?text='.rawurlencode(implode("\n", $messageLines));

        return view('purchases.show', compact(
            'orders',
            'invoiceNumber',
            'total',
            'status',
            'paymentStatus',
            'paymentVerifiedAt',
            'paymentVerifier',
            'canConfirmReceipt',
            'whatsAppUrl',
        ));
    }

    public function cancel(Request $request, string $invoiceNumber): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $result = DB::transaction(function () use ($user, $invoiceNumber): array {
            $orders = $user->orders()
                ->where('invoice_number', $invoiceNumber)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return ['status' => 'not_found'];
            }

            if ($orders->contains(fn (Order $order): bool => $order->status !== Order::STATUS_PENDING)) {
                return ['status' => 'not_cancellable'];
            }

            $products = Product::query()
                ->whereIn('id', $orders->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($orders as $order) {
                $product = $products->get($order->product_id);
                if ($product !== null) {
                    $product->increment('stock', $order->quantity);
                }
            }

            $user->orders()
                ->where('invoice_number', $invoiceNumber)
                ->update(['status' => Order::STATUS_CANCELLED]);

            return ['status' => 'cancelled'];
        });

        if ($result['status'] === 'not_found') {
            abort(404);
        }

        if ($result['status'] === 'not_cancellable') {
            return to_route('purchases.show', $invoiceNumber)
                ->with('error', 'Pesanan hanya bisa dibatalkan saat statusnya masih Pending.');
        }

        return to_route('purchases.show', $invoiceNumber)
            ->with('success', 'Pesanan berhasil dibatalkan dan stok produk telah dikembalikan.');
    }

    public function complete(Request $request, string $invoiceNumber): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        $result = DB::transaction(function () use ($user, $invoiceNumber): string {
            $orders = $user->orders()
                ->where('invoice_number', $invoiceNumber)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return 'not_found';
            }

            if ($orders->every(fn (Order $order): bool => $order->status === Order::STATUS_COMPLETED)) {
                return 'already_completed';
            }

            if ($orders->contains(fn (Order $order): bool => $order->status !== Order::STATUS_PROCESSING
                || $order->payment_status !== Order::PAYMENT_PAID)) {
                return 'not_confirmable';
            }

            $user->orders()
                ->where('invoice_number', $invoiceNumber)
                ->update(['status' => Order::STATUS_COMPLETED]);

            return 'completed';
        });

        if ($result === 'not_found') {
            abort(404);
        }

        if ($result === 'already_completed') {
            return to_route('purchases.show', $invoiceNumber)
                ->with('success', 'Penerimaan invoice ini sudah dikonfirmasi sebelumnya.');
        }

        if ($result !== 'completed') {
            return to_route('purchases.show', $invoiceNumber)
                ->with('error', 'Pesanan hanya bisa dikonfirmasi setelah pembayaran diverifikasi dan statusnya Processing.');
        }

        return to_route('purchases.show', $invoiceNumber)
            ->with('success', 'Penerimaan pesanan berhasil dikonfirmasi. Status pesanan menjadi Completed.');
    }
}
