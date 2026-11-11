<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Orders</h2>
            <p class="mt-1 text-sm text-gray-500">Riwayat seluruh invoice pesanan pelanggan.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-200 px-4 py-5 sm:flex-row sm:items-end sm:justify-between sm:px-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Latest Orders</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $invoices->total() }} invoice tercatat.</p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Kembali ke Dashboard <span aria-hidden="true">&rarr;</span></a>
                </div>
                <form method="GET" action="{{ route('dashboard.orders.index') }}" class="admin-orders-filter-form border-b border-gray-200 px-4 py-5 sm:px-6">
                    <label class="block text-sm font-semibold text-gray-700">
                        Tanggal
                        <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                    </label>
                    <label class="block text-sm font-semibold text-gray-700">
                        Bulan
                        <select name="month" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Semua bulan</option>
                            @foreach ($months as $number => $name)
                                <option value="{{ $number }}" @selected((string) ($filters['month'] ?? '') === (string) $number)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-gray-700">
                        Tahun
                        <input type="number" name="year" min="2000" max="2100" value="{{ $filters['year'] ?? '' }}" placeholder="Contoh: 2026" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                    </label>
                    <label class="block text-sm font-semibold text-gray-700">
                        Status
                        <select name="status" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Semua status</option>
                            @foreach (['pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="admin-orders-filter-actions">
                        <div><button type="submit" class="admin-filter-button">Filter</button></div>
                        <div><a href="{{ route('dashboard.orders.index') }}" class="admin-reset-button">Reset</a></div>
                    </div>
                </form>
                @if ($errors->any())
                    <div class="mx-4 mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 sm:mx-6" role="alert">
                        Periksa kembali filter tanggal, bulan, tahun, atau status yang dipilih.
                    </div>
                @endif
                <div class="admin-orders-scroll w-full overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;" tabindex="0" aria-label="Riwayat pesanan, geser ke samping untuk melihat kolom lainnya">
                    <p class="px-4 pt-3 text-xs text-gray-500 sm:hidden">Geser tabel ke samping untuk melihat kolom lainnya.</p>
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
                            @forelse ($invoices as $invoice)
                                <tr>
                                    <td class="sticky left-0 z-10 whitespace-nowrap bg-white px-4 py-4 text-sm font-semibold text-gray-900 shadow-[2px_0_6px_-3px_rgba(23,32,51,0.25)] sm:px-6">{{ $invoice['invoice_number'] }}</td>
                                    <td class="min-w-48 px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $invoice['product'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $invoice['customer'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm text-gray-700 sm:px-6">{{ number_format($invoice['quantity']) }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-gray-900 sm:px-6">Rp {{ number_format($invoice['total'], 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $invoice['status'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-700 sm:px-6">{{ $invoice['payment_status'] }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-600 sm:px-6">{{ $invoice['created_at']?->format('d-m-Y H:i') ?? '-' }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm sm:px-6">
                                        @if ($invoice['is_cancelled'])
                                            <span class="text-xs font-medium text-red-700" role="status">Pesanan Dibatalkan</span>
                                        @elseif ($invoice['can_verify_payment'])
                                            <div class="flex flex-col items-stretch gap-2">
                                                <form method="POST" action="{{ route('dashboard.orders.accept', ['invoiceNumber' => $invoice['invoice_number'], 'return_to' => 'orders']) }}" onsubmit="return confirm('Pastikan kamu sudah memeriksa bukti transfer atau konfirmasi pembayaran pelanggan di WhatsApp.')">
                                                    @csrf
                                                    @foreach ($filters as $key => $value)
                                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                    @endforeach
                                                    <button type="submit" class="admin-order-action admin-order-action-accept">Terima pesanan</button>
                                                </form>
                                                <form method="POST" action="{{ route('dashboard.orders.cancel', ['invoiceNumber' => $invoice['invoice_number'], 'return_to' => 'orders']) }}" onsubmit="return confirm('Yakin ingin membatalkan invoice ini? Status semua item akan menjadi Cancelled dan stok dikembalikan.')">
                                                    @csrf
                                                    @foreach ($filters as $key => $value)
                                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                    @endforeach
                                                    <button type="submit" class="admin-order-action admin-order-action-cancel">Batalkan pesanan</button>
                                                </form>
                                            </div>
                                        @elseif ($invoice['status'] === 'Completed')
                                            <span class="text-xs font-medium text-green-700" role="status">Pesanan Selesai</span>
                                        @elseif ($invoice['payment_status'] === 'Terverifikasi')
                                            <span class="text-xs font-medium text-green-700">Diverifikasi Admin</span>
                                        @else
                                            <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada pesanan pelanggan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($invoices->hasPages())
                    <div class="border-t border-gray-200 px-4 py-4 sm:px-6">{{ $invoices->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
