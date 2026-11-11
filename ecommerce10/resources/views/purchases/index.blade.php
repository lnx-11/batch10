<x-layout title="Riwayat Pembelian - LИX.goods">
    <section class="page-heading pb-4">
        <span class="eyebrow">Akunmu</span>
        <h1 class="mt-2 mb-2">Riwayat pembelian.</h1>
        <p class="text-secondary mb-0">Pesanan yang sudah kamu buat tersimpan di sini.</p>
    </section>

    @if ($invoices->isEmpty())
        <div class="empty-cart">
            <div class="benefit-icon mx-auto mb-3" style="width:58px;height:58px;font-size:1.5rem" aria-hidden="true">&#128722;</div>
            <h2 class="h4 fw-bold">Belum ada pembelian</h2>
            <p class="text-secondary">Pesananmu akan terlihat di sini setelah checkout.</p>
            <a href="{{ route('products.index') }}" class="btn btn-brand mt-2">Jelajahi produk <span aria-hidden="true">&rarr;</span></a>
        </div>
    @else
        <div class="cart-panel p-0 overflow-hidden">
            <div class="p-4 border-bottom">
                <h2 class="h5 fw-bold mb-1">Pesananmu</h2>
                <span class="small text-secondary">{{ $invoices->total() }} invoice tercatat</span>
            </div>
            <div class="table-responsive px-2 px-md-3">
                <table class="table cart-table mb-0">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Tanggal</th>
                            <th class="text-end">Jumlah item</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th class="text-end">Total</th>
                            <th><span class="visually-hidden">Detail</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="text-nowrap fw-bold">{{ $invoice->invoice_number }}</td>
                                <td class="text-nowrap">{{ $invoice->created_at->format('d M Y, H:i') }}</td>
                                <td class="text-end">{{ $invoice->item_count }}</td>
                                <td><span class="order-status-badge order-status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                                <td><span class="badge rounded-pill {{ $invoice->payment_status === 'paid' ? 'text-bg-success' : 'text-bg-light border text-dark' }}">{{ $invoice->payment_status === 'paid' ? 'Terverifikasi' : 'Belum diverifikasi' }}</span></td>
                                <td class="text-end fw-bold text-nowrap">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('purchases.show', $invoice->invoice_number) }}" class="btn btn-sm btn-outline-brand">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($invoices->hasPages())
                <div class="p-4 border-top catalog-pagination">{{ $invoices->links() }}</div>
            @endif
        </div>

        <p class="small text-secondary mt-3 mb-0">Pesanan yang tampil sudah tercatat di toko. Konfirmasi dan detail pengiriman dibicarakan melalui WhatsApp.</p>
    @endif
</x-layout>
