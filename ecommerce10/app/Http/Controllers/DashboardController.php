<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function acceptOrder(Request $request, string $invoiceNumber): RedirectResponse
    {
        $result = DB::transaction(function () use ($invoiceNumber): string {
            $orders = Order::query()
                ->where('invoice_number', $invoiceNumber)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return 'not_found';
            }

            if ($orders->contains(fn (Order $order): bool => $order->status === Order::STATUS_CANCELLED)) {
                return 'cancelled';
            }

            if ($orders->every(fn (Order $order): bool => $order->payment_status === Order::PAYMENT_PAID)) {
                return 'already_verified';
            }

            if ($orders->contains(fn (Order $order): bool => $order->status !== Order::STATUS_PENDING)) {
                return 'not_pending';
            }

            $now = now();
            Order::query()
                ->where('invoice_number', $invoiceNumber)
                ->update([
                    'payment_status' => Order::PAYMENT_PAID,
                    'payment_verified_at' => $now,
                    'payment_verified_by' => auth()->id(),
                    'status' => Order::STATUS_PROCESSING,
                ]);

            return 'verified';
        });

        if ($result === 'not_found') {
            abort(404);
        }

        if ($result === 'already_verified') {
            return $this->ordersRedirect($request)->with('success', 'Invoice '.$invoiceNumber.' sudah diterima dan diverifikasi.');
        }

        if ($result !== 'verified') {
            return $this->ordersRedirect($request)->with('error', 'Invoice '.$invoiceNumber.' tidak dapat diterima. Pastikan statusnya masih Pending dan belum dibatalkan.');
        }

        return $this->ordersRedirect($request)->with('success', 'Invoice '.$invoiceNumber.' diterima. Status pesanan menjadi Processing dan pembayaran dicatat terverifikasi secara manual.');
    }

    public function cancelOrder(Request $request, string $invoiceNumber): RedirectResponse
    {
        $result = DB::transaction(function () use ($invoiceNumber): string {
            $orders = Order::query()
                ->where('invoice_number', $invoiceNumber)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return 'not_found';
            }

            if ($orders->contains(fn (Order $order): bool => $order->status !== Order::STATUS_PENDING)) {
                return 'not_pending';
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

            Order::query()
                ->where('invoice_number', $invoiceNumber)
                ->update(['status' => Order::STATUS_CANCELLED]);

            return 'cancelled';
        });

        if ($result === 'not_found') {
            abort(404);
        }

        if ($result !== 'cancelled') {
            return $this->ordersRedirect($request)->with('error', 'Invoice '.$invoiceNumber.' tidak dapat dibatalkan dari dashboard karena statusnya bukan Pending.');
        }

        return $this->ordersRedirect($request)->with('success', 'Invoice '.$invoiceNumber.' dibatalkan oleh admin dan stok produk telah dikembalikan.');
    }

    private function ordersRedirect(Request $request): RedirectResponse
    {
        if ($request->query('return_to') !== 'orders') {
            return to_route('dashboard');
        }

        $filters = array_filter(
            $request->only(['date', 'month', 'year', 'status']),
            fn (mixed $value): bool => $value !== null && $value !== '',
        );

        return to_route('dashboard.orders.index', $filters);
    }

    public function orders(Request $request): View
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'status' => ['nullable', 'in:'.implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_PROCESSING,
                Order::STATUS_COMPLETED,
                Order::STATUS_CANCELLED,
            ])],
        ]);
        $query = Order::query()
            ->whereNotNull('invoice_number')
            ->select('invoice_number')
            ->groupBy('invoice_number');

        if (isset($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (isset($filters['month'])) {
            $query->whereMonth('created_at', $filters['month']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $invoices = $query
            ->orderByRaw('MAX(created_at) DESC')
            ->paginate(15)
            ->withQueryString();
        $invoiceNumbers = $invoices->getCollection()->pluck('invoice_number');
        $orderLines = Order::query()
            ->with('user')
            ->whereIn('invoice_number', $invoiceNumbers)
            ->orderBy('id')
            ->get()
            ->groupBy('invoice_number');
        $invoices->setCollection($invoices->getCollection()->map(function (Order $invoice) use ($orderLines): array {
            $invoiceNumber = $invoice->invoice_number;
            $invoiceOrders = $orderLines->get($invoiceNumber);
            $firstOrder = $invoiceOrders->first();

            return [
                'invoice_number' => $invoiceNumber,
                'product' => $invoiceOrders
                    ->map(fn (Order $order) => $order->product?->name ?? 'Produk dihapus')
                    ->unique()
                    ->implode(', '),
                'customer' => $firstOrder->user?->name ?? 'Pelanggan dihapus',
                'recipient_name' => $firstOrder->recipient_name,
                'recipient_phone' => $firstOrder->recipient_phone,
                'shipping_address' => $firstOrder->formattedShippingAddress(),
                'quantity' => $invoiceOrders->sum('quantity'),
                'total' => $invoiceOrders->sum('total'),
                'status' => ucfirst($firstOrder->status),
                'is_cancelled' => $invoiceOrders->every(fn (Order $order): bool => $order->status === Order::STATUS_CANCELLED),
                'payment_status' => $firstOrder->payment_status === Order::PAYMENT_PAID
                    ? 'Terverifikasi'
                    : 'Belum diverifikasi',
                'can_verify_payment' => $invoiceOrders->every(fn (Order $order): bool => $order->status === Order::STATUS_PENDING
                    && $order->payment_status === Order::PAYMENT_UNPAID),
                'created_at' => $firstOrder->created_at,
            ];
        }));

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return view('admin.orders.index', compact('invoices', 'filters', 'months'));
    }

    public function index(): View
    {
        $productCount = Product::query()->count();
        $categoryCount = ProductCategory::query()->count();
        $userCount = User::query()->count();
        $productClickCount = Product::query()->sum('click_count');
        $revenueTotal = Order::query()
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->sum('total');
        $startDate = now()->startOfDay()->subDays(6);
        $endDate = now();
        $categoryClicks = ProductCategory::query()
            ->withSum('products', 'click_count')
            ->orderBy('name')
            ->get();
        $clickChart = [
            'labels' => $categoryClicks->pluck('name')->all(),
            'values' => $categoryClicks->map(fn (ProductCategory $category) => (int) ($category->products_sum_click_count ?? 0))->all(),
        ];
        $isSampleClickChart = array_sum($clickChart['values']) === 0;

        if ($isSampleClickChart) {
            $sampleClicks = [18, 12, 9, 6, 4];
            $clickChart['values'] = $categoryClicks
                ->map(fn (ProductCategory $category, int $index) => $sampleClicks[$index] ?? max(2, 10 - $index))
                ->all();
        }
        $invoiceNumbers = Order::query()
            ->whereNotNull('invoice_number')
            ->select('invoice_number')
            ->groupBy('invoice_number')
            ->orderByRaw('MAX(created_at) DESC')
            ->limit(8)
            ->pluck('invoice_number');
        $orders = Order::query()
            ->with(['product', 'user'])
            ->whereIn('invoice_number', $invoiceNumbers)
            ->orderBy('id')
            ->get()
            ->groupBy('invoice_number');
        $isSampleLatestOrders = $invoiceNumbers->isEmpty();
        $latestOrders = $invoiceNumbers->map(function (string $invoiceNumber) use ($orders): array {
            $invoiceOrders = $orders->get($invoiceNumber);
            $firstOrder = $invoiceOrders->first();

            return [
                'id' => $invoiceNumber,
                'product' => $invoiceOrders
                    ->map(fn (Order $order) => $order->product?->name ?? 'Produk dihapus')
                    ->unique()
                    ->implode(', '),
                'customer' => $firstOrder->user?->name ?? 'Pelanggan dihapus',
                'quantity' => $invoiceOrders->sum('quantity'),
                'total' => $invoiceOrders->sum('total'),
                'status' => ucfirst($firstOrder->status),
                'is_cancelled' => $firstOrder->status === Order::STATUS_CANCELLED,
                'payment_status' => $firstOrder->payment_status === Order::PAYMENT_PAID
                    ? 'Terverifikasi'
                    : 'Belum diverifikasi',
                'can_verify_payment' => $firstOrder->status === Order::STATUS_PENDING
                    && $firstOrder->payment_status === Order::PAYMENT_UNPAID,
                'created_at' => $firstOrder->created_at,
            ];
        });
        $orderStatuses = [
            Order::STATUS_PENDING => 'Pending',
            Order::STATUS_PROCESSING => 'Processing',
            Order::STATUS_COMPLETED => 'Completed',
            Order::STATUS_CANCELLED => 'Cancelled',
        ];
        $orderStatusCounts = Order::query()
            ->selectRaw('status, COUNT(DISTINCT invoice_number) as invoice_count')
            ->groupBy('status')
            ->pluck('invoice_count', 'status');
        $orderStatusChart = [
            'labels' => array_values($orderStatuses),
            'values' => array_map(
                fn (string $status): int => (int) ($orderStatusCounts[$status] ?? 0),
                array_keys($orderStatuses),
            ),
        ];
        $isSampleOrderStatusChart = $isSampleLatestOrders;

        if ($isSampleLatestOrders) {
            $orderStatusChart['values'] = [2, 1, 2, 1];
            $sampleProducts = Product::query()->orderBy('id')->limit(5)->get();
            $sampleCustomerNames = ['Agus Setiawan', 'Siti Aminah', 'Budi Santoso', 'Dewi Lestari', 'Rina Maharani'];
            $sampleStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled', 'Pending'];
            $samplePaymentStatuses = ['Belum diverifikasi', 'Terverifikasi', 'Terverifikasi', 'Belum diverifikasi', 'Terverifikasi'];
            $latestOrders = $sampleProducts
                ->map(fn (Product $product, int $index) => [
                    'id' => 'INV-'.['6339', '8100', '2558', '6396', '9548'][$index],
                    'product' => $product->name,
                    'customer' => $sampleCustomerNames[$index],
                    'quantity' => $index % 2 + 1,
                    'total' => $product->price * ($index % 2 + 1),
                    'status' => $sampleStatuses[$index],
                    'is_cancelled' => $sampleStatuses[$index] === 'Cancelled',
                    'payment_status' => $samplePaymentStatuses[$index],
                    'can_verify_payment' => false,
                    'created_at' => now()->subDays($index),
                ]);
        }
        $isSampleOrderChart = $isSampleLatestOrders;

        if ($isSampleOrderChart) {
            $dailyOrders = $latestOrders
                ->groupBy(fn (array $order) => $order['created_at']->toDateString())
                ->map(fn ($daily) => [
                    'order_count' => $daily->count(),
                    'revenue' => $daily->sum('total'),
                ]);
        } else {
            $dailyOrders = Order::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as order_date, COUNT(DISTINCT invoice_number) as order_count, SUM(CASE WHEN status != ? THEN total ELSE 0 END) as revenue', [Order::STATUS_CANCELLED])
                ->groupBy('order_date')
                ->get()
                ->keyBy('order_date');
        }

        $orderChart = collect(range(0, 6))
            ->map(function (int $offset) use ($startDate, $dailyOrders) {
                $date = $startDate->copy()->addDays($offset);
                $daily = $dailyOrders->get($date->toDateString());

                return [
                    'label' => $date->format('d M'),
                    'orders' => (int) data_get($daily, 'order_count', 0),
                    'revenue' => (int) data_get($daily, 'revenue', 0),
                ];
            });

        return view('admin.dashboard', compact(
            'productCount',
            'categoryCount',
            'userCount',
            'productClickCount',
            'revenueTotal',
            'orderChart',
            'isSampleOrderChart',
            'clickChart',
            'isSampleClickChart',
            'latestOrders',
            'isSampleLatestOrders',
            'orderStatusChart',
            'isSampleOrderStatusChart',
        ));
    }
}
