<x-layout title="Invoice {{ $invoiceNumber }} - LИX.goods">
    <section class="page-heading pb-4">
        <span class="eyebrow">Detail pembelian</span>
        <h1 class="mt-2 mb-2">Invoice {{ $invoiceNumber }}.</h1>
        <p class="text-secondary mb-0">Dibuat pada {{ $orders->first()->created_at->format('d M Y, H:i') }}.</p>
    </section>

    @if (session('success'))
        <div class="alert alert-success rounded-3" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger rounded-3" role="alert">{{ session('error') }}</div>
    @endif

    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <div class="cart-panel p-0 overflow-hidden">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold mb-1">Produk dalam pesanan</h2>
                    <span class="small text-secondary">Semua produk berikut tercatat dalam satu invoice.</span>
                </div>
                <div class="table-responsive px-2 px-md-3">
                    <table class="table cart-table mb-0">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="fw-bold">{{ $order->product?->name ?? 'Produk tidak tersedia' }}</td>
                                    <td class="text-end">{{ $order->quantity }}</td>
                                    <td class="text-end fw-bold text-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cart-panel">
                <h2 class="h5 fw-bold mb-3">Ringkasan invoice</h2>
                <div class="d-flex justify-content-between text-secondary mb-3">
                    <span>Nomor invoice</span>
                    <span class="fw-semibold text-dark">{{ $invoiceNumber }}</span>
                </div>
                <div class="d-flex justify-content-between text-secondary mb-3">
                    <span>Status pesanan</span>
                    <span class="order-status-badge order-status-{{ $status }}">{{ ucfirst($status) }}</span>
                </div>
                <div class="d-flex justify-content-between text-secondary mb-3">
                    <span>Status pembayaran</span>
                    <span class="badge rounded-pill {{ $paymentStatus === 'paid' ? 'text-bg-success' : 'text-bg-light border text-dark' }}">{{ $paymentStatus === 'paid' ? 'Terverifikasi' : 'Belum diverifikasi' }}</span>
                </div>
                @if ($paymentStatus === 'paid' && $paymentVerifiedAt)
                    <p class="small text-secondary mb-3">
                        Diverifikasi admin{{ $paymentVerifier ? ' '.$paymentVerifier->name : '' }}
                        pada {{ $paymentVerifiedAt->format('d M Y, H:i') }}.
                    </p>
                @endif
                <div class="cart-total d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold">Total</span>
                    <span class="product-price">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                @if ($status === 'pending')
                    <p class="small text-secondary">Hubungi toko melalui WhatsApp untuk konfirmasi pembayaran. Pesanan akan diproses setelah admin memeriksa dan memverifikasi pembayaran. Kamu bisa membatalkan selama status masih Pending.</p>
                    <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp w-100 mb-2">
                        <svg class="whatsapp-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M12.04 2a9.87 9.87 0 0 0-8.45 15l-1.3 4.75 4.86-1.27A9.93 9.93 0 1 0 12.04 2Zm0 18.05a8.1 8.1 0 0 1-4.13-1.13l-.3-.18-2.88.75.77-2.8-.2-.3a8.12 8.12 0 1 1 6.74 3.66Zm4.45-6.08c-.24-.12-1.43-.71-1.65-.79-.22-.08-.38-.12-.54.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.43-1.34-1.67-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.46-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.1.15 1.52.09.46-.07 1.43-.59 1.63-1.15.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28Z"/>
                        </svg>
                        <span>Konfirmasi via WhatsApp</span>
                    </a>
                    <form method="POST" action="{{ route('purchases.cancel', $invoiceNumber) }}" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">Batalkan pesanan</button>
                    </form>
                @elseif ($status === 'processing' && $canConfirmReceipt)
                    <p class="small text-secondary">Jika pesanan sudah kamu terima, konfirmasikan di bawah. Jika belum dikonfirmasi, pesanan otomatis menjadi Completed setelah 5 hari sejak mulai diproses.</p>
                    <form method="POST" action="{{ route('purchases.complete', $invoiceNumber) }}" onsubmit="return confirm('Pastikan semua barang dalam pesanan ini sudah kamu terima.')">
                        @csrf
                        <button type="submit" class="btn btn-brand w-100">Konfirmasi pesanan diterima</button>
                    </form>
                @elseif ($status === 'completed')
                    <p class="small text-secondary">Penerimaan pesanan ini sudah kamu konfirmasi.</p>
                @else
                    <p class="small text-secondary">Pesanan ini sudah dibatalkan dan stok telah dikembalikan.</p>
                @endif
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-brand w-100 mt-2">Kembali ke riwayat</a>
            </div>
        </div>
    </div>
</x-layout>
