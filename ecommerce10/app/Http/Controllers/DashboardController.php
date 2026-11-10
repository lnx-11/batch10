<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $productCount = Product::query()->count();
        $categoryCount = ProductCategory::query()->count();
        $userCount = User::query()->count();
        $productClickCount = Product::query()->sum('click_count');
        $revenueTotal = Order::query()->sum('total');
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
        $orders = Order::query()
            ->with(['product', 'user'])
            ->latest('created_at')
            ->latest('id')
            ->limit(8)
            ->get();
        $isSampleLatestOrders = $orders->isEmpty();
        $latestOrders = $orders->map(fn (Order $order) => [
            'id' => '#'.$order->id,
            'product' => $order->product?->name ?? 'Produk dihapus',
            'customer' => $order->user?->name ?? 'Pelanggan dihapus',
            'quantity' => $order->quantity,
            'total' => $order->total,
            'status' => 'Belum tersedia',
            'payment_status' => 'Belum tersedia',
            'created_at' => $order->created_at,
        ]);
        $orderStatusChart = [
            'labels' => ['Pending', 'Processing', 'Completed', 'Cancelled'],
            'values' => [2, 1, 2, 1],
        ];
        $isSampleOrderStatusChart = $isSampleLatestOrders;

        if ($isSampleLatestOrders) {
            $sampleProducts = Product::query()->orderBy('id')->limit(5)->get();
            $sampleCustomerNames = ['Agus Setiawan', 'Siti Aminah', 'Budi Santoso', 'Dewi Lestari', 'Rina Maharani'];
            $sampleStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled', 'Pending'];
            $samplePaymentStatuses = ['Unpaid', 'Paid', 'Paid', 'Unpaid', 'Paid'];
            $latestOrders = $sampleProducts
                ->map(fn (Product $product, int $index) => [
                    'id' => 'INV-'.['6339', '8100', '2558', '6396', '9548'][$index],
                    'product' => $product->name,
                    'customer' => $sampleCustomerNames[$index],
                    'quantity' => $index % 2 + 1,
                    'total' => $product->price * ($index % 2 + 1),
                    'status' => $sampleStatuses[$index],
                    'payment_status' => $samplePaymentStatuses[$index],
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
                ->selectRaw('DATE(created_at) as order_date, COUNT(*) as order_count, SUM(total) as revenue')
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
