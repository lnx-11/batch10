<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Dashboard</h2>
            <p class="mt-1 text-sm text-gray-500">Ringkasan aktivitas toko LИX.goods.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">{{ session('error') }}</div>
            @endif
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 sm:gap-5" aria-label="Ringkasan toko">
                <article class="flex items-center justify-between rounded-2xl bg-white p-4 shadow-sm sm:p-6">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Jumlah Produk</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($productCount) }}</p>
                    </div>
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-indigo-50 text-indigo-600" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9-4 9 4-9 4-9-4Zm0 0v10l9 4 9-4V7M12 11v10"/></svg>
                    </span>
                </article>
                <article class="flex items-center justify-between rounded-2xl bg-white p-4 shadow-sm sm:p-6">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Jumlah Kategori</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($categoryCount) }}</p>
                    </div>
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-violet-50 text-violet-600" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    </span>
                </article>
                <article class="flex items-center justify-between rounded-2xl bg-white p-4 shadow-sm sm:p-6">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Jumlah Klik Produk</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($productClickCount) }}</p>
                    </div>
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-amber-50 text-amber-600" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m13 2-3 8h7l-6 12 1-9H5l8-11Z"/></svg>
                    </span>
                </article>
                <article class="flex items-center justify-between rounded-2xl bg-white p-4 shadow-sm sm:p-6">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Users</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($userCount) }}</p>
                    </div>
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-sky-50 text-sky-600" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.87M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7.87a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                </article>
                <article class="flex items-center justify-between rounded-2xl bg-white p-4 shadow-sm sm:p-6">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Revenue</p>
                        <p class="mt-2 text-xl font-bold text-gray-900 sm:text-2xl">Rp {{ number_format($revenueTotal, 0, ',', '.') }}</p>
                    </div>
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-emerald-50 text-emerald-600" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2.5" y="5" width="19" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M6 9h.01M18 15h.01"/></svg>
                    </span>
                </article>
            </section>

            <section class="rounded-2xl bg-white p-4 shadow-sm sm:p-8" aria-label="Grafik toko">
                <div class="mb-10 border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-bold text-gray-900">Analisis Toko</h3>
                    <p class="mt-1 text-sm text-gray-500">Ringkasan pesanan, status, dan interaksi produk.</p>
                </div>
                <div class="space-y-14">
                    <article class="min-w-0">
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-gray-900">Pesanan 7 Hari Terakhir</h3>
                            <p class="mt-1 text-sm text-gray-500">Jumlah pesanan dan pendapatan per hari.</p>
                            @if ($isSampleOrderChart)
                                <p class="mt-2 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Data contoh grafik — belum ada pesanan dalam 7 hari terakhir</p>
                            @endif
                        </div>
                        <div class="relative mt-8 h-64 sm:mt-12 sm:h-80">
                            <canvas
                                aria-label="Grafik jumlah pesanan dan pendapatan dalam tujuh hari terakhir"
                                role="img"
                                data-dashboard-orders
                                data-labels="{{ json_encode($orderChart->pluck('label')->all()) }}"
                                data-orders="{{ json_encode($orderChart->pluck('orders')->all()) }}"
                                data-revenue="{{ json_encode($orderChart->pluck('revenue')->all()) }}"
                            ></canvas>
                        </div>
                    </article>
                    <div class="grid gap-8 lg:grid-cols-2">
                        <article class="min-w-0">
                            <div class="mb-8">
                                <h3 class="text-lg font-bold text-gray-900">Order Status</h3>
                                <p class="mt-1 text-sm text-gray-500">Ringkasan status pesanan.</p>
                                @if ($isSampleOrderStatusChart)
                                    <p class="mt-2 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Data contoh status — belum ada pesanan sebenarnya</p>
                                @endif
                            </div>
                            @if ($isSampleOrderStatusChart)
                                <div class="relative mx-auto mt-8 h-64 max-w-lg sm:mt-12 sm:h-72">
                                    <canvas
                                        aria-label="Grafik contoh status pesanan"
                                        role="img"
                                        data-dashboard-order-status
                                        data-labels="{{ json_encode($orderStatusChart['labels']) }}"
                                        data-values="{{ json_encode($orderStatusChart['values']) }}"
                                    ></canvas>
                                </div>
                            @else
                                <div class="grid h-72 place-items-center rounded-xl bg-gray-50 px-6 text-center text-sm text-gray-500">
                                    <canvas
                                        aria-label="Grafik status pesanan"
                                        role="img"
                                        data-dashboard-order-status
                                        data-labels="{{ json_encode($orderStatusChart['labels']) }}"
                                        data-values="{{ json_encode($orderStatusChart['values']) }}"
                                    ></canvas>
                                </div>
                            @endif
                        </article>
                        <article class="min-w-0">
                            <div class="mb-8">
                                <h3 class="text-lg font-bold text-gray-900">Klik Produk per Kategori</h3>
                                <p class="mt-1 text-sm text-gray-500">Sebaran klik produk di setiap kategori.</p>
                                @if ($isSampleClickChart)
                                    <p class="mt-2 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Data contoh klik — belum ada klik sebenarnya</p>
                                @endif
                            </div>
                            @if (count($clickChart['values']) > 0)
                                <div class="relative mx-auto mt-8 h-64 max-w-lg sm:mt-12 sm:h-72">
                                    <canvas
                                        aria-label="Grafik sebaran klik produk menurut kategori"
                                        role="img"
                                        data-dashboard-clicks
                                        data-labels="{{ json_encode($clickChart['labels']) }}"
                                        data-values="{{ json_encode($clickChart['values']) }}"
                                    ></canvas>
                                </div>
                            @else
                                <div class="mt-8 grid h-64 place-items-center rounded-xl bg-gray-50 px-6 text-center text-sm text-gray-500 sm:mt-12 sm:h-72">
                                    Belum ada kategori produk untuk ditampilkan.
                                </div>
                            @endif
                        </article>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-5 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Latest Orders</h3>
                        <p class="mt-1 text-sm text-gray-500">Pesanan produk terbaru yang tercatat di toko.</p>
                        @if ($isSampleLatestOrders)
                            <p class="mt-2 inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">Data contoh pesanan — belum ada pesanan sebenarnya</p>
                        @endif
                    </div>
                    <a href="{{ route('dashboard.products.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Kelola produk <span aria-hidden="true">&rarr;</span></a>
                </div>
                <div class="admin-orders-scroll w-full overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;" tabindex="0" aria-label="Tabel pesanan terbaru, geser ke samping untuk melihat kolom lainnya">
                    <p class="px-4 pt-3 text-xs text-gray-500 sm:hidden">Geser tabel ke samping <span class="font-semibold text-indigo-600" aria-hidden="true">&rarr;</span> untuk melihat kolom lainnya.</p>
                    <table class="w-full min-w-[1120px] divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="sticky left-0 z-10 whitespace-nowrap bg-gray-50 px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 shadow-[2px_0_6px_-3px_rgba(23,32,51,0.25)] sm:px-6">Invoice</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 sm:px-6">Produk</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 sm:px-6">Pelanggan</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 sm:px-6">Jumlah</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 sm:px-6">Total</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 sm:px-6">Status</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 sm:px-6">Status Pembayaran</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 sm:px-6">Tanggal</th>
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 sm:px-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($latestOrders as $order)
                                <tr>
                                    <td class="sticky left-0 z-10 whitespace-nowrap bg-white px-4 py-4 text-sm font-semibold text-gray-900 shadow-[2px_0_6px_-3px_rgba(23,32,51,0.25)] sm:px-6">{{ $order['id'] }}</td>
                                    <td class="min-w-48 px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $order['product'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $order['customer'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700 sm:px-6">{{ number_format($order['quantity']) }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900 sm:px-6">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $order['status'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $order['payment_status'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 sm:px-6">{{ $order['created_at']?->format('d M Y | H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm sm:px-6">
                                        @if ($order['is_cancelled'])
                                            <span class="text-xs font-medium text-red-700" role="status">Pesanan Dibatalkan</span>
                                        @elseif ($order['can_verify_payment'])
                                            <div class="flex flex-col items-stretch gap-2">
                                                <form method="POST" action="{{ route('dashboard.orders.accept', $order['id']) }}" onsubmit="return confirm('Pastikan kamu sudah memeriksa bukti transfer atau konfirmasi pembayaran pelanggan di WhatsApp.')">
                                                    @csrf
                                                    <button type="submit" class="admin-order-action admin-order-action-accept">Terima pesanan</button>
                                                </form>
                                                <form method="POST" action="{{ route('dashboard.orders.cancel', $order['id']) }}" onsubmit="return confirm('Yakin ingin membatalkan invoice ini? Status semua item akan menjadi Cancelled dan stok dikembalikan.')">
                                                    @csrf
                                                    <button type="submit" class="admin-order-action admin-order-action-cancel">Batalkan pesanan</button>
                                                </form>
                                            </div>
                                        @elseif ($order['status'] === 'Completed')
                                            <span class="text-xs font-medium text-green-700" role="status">Pesanan Selesai</span>
                                        @elseif ($order['payment_status'] === 'Terverifikasi')
                                            <span class="text-xs font-medium text-green-700">Diverifikasi Admin</span>
                                        @else
                                            <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada pesanan produk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
