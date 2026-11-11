<x-layout title="Checkout - LИX.goods">
    <section class="page-heading pb-4">
        <span class="eyebrow">Langkah terakhir</span>
        <h1 class="mt-2 mb-2">Periksa pesananmu.</h1>
        <p class="text-secondary mb-0">Pesanan akan dicatat dan stok diperbarui saat kamu melanjutkan.</p>
    </section>

    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <div class="cart-panel p-0 overflow-hidden">
                <div class="p-4 border-bottom">
                    <h2 class="h5 fw-bold mb-1">Daftar produk</h2>
                    <span class="small text-secondary">Pesanan untuk {{ auth()->user()->name }}</span>
                </div>
                <div class="table-responsive px-2 px-md-3">
                    <table class="table cart-table mb-0">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="fw-bold">{{ $item->product->name }}</td>
                                    <td class="text-secondary text-nowrap">Rp {{ number_format($item->product->price, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end fw-bold text-nowrap">Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="cart-panel">
                <h2 class="h5 fw-bold mb-3">Ringkasan checkout</h2>
                <div class="d-flex justify-content-between text-secondary mb-3">
                    <span>Total item</span>
                    <span>{{ $items->sum('quantity') }} item</span>
                </div>
                <div class="cart-total d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold">Total harga</span>
                    <span class="product-price">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <p class="small text-secondary">Saat dikonfirmasi, pesanan tersimpan dan stok produk berkurang. WhatsApp akan terbuka dengan rincian pesanan; tekan Kirim di WhatsApp untuk mengonfirmasi.</p>
                <form method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <button type="submit" class="btn btn-whatsapp w-100">
                        <svg class="whatsapp-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path fill="currentColor" d="M12.04 2a9.87 9.87 0 0 0-8.45 15l-1.3 4.75 4.86-1.27A9.93 9.93 0 1 0 12.04 2Zm0 18.05a8.1 8.1 0 0 1-4.13-1.13l-.3-.18-2.88.75.77-2.8-.2-.3a8.12 8.12 0 1 1 6.74 3.66Zm4.45-6.08c-.24-.12-1.43-.71-1.65-.79-.22-.08-.38-.12-.54.12-.16.24-.62.79-.76.95-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.43-1.34-1.67-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.46-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.1.15 1.52.09.46-.07 1.43-.59 1.63-1.15.2-.57.2-1.06.14-1.16-.06-.1-.22-.16-.46-.28Z"/>
                        </svg>
                        <span>Konfirmasi pesanan via WhatsApp</span>
                    </button>
                </form>
                <a href="{{ route('cart.index') }}" class="btn btn-outline-brand w-100 mt-2">Kembali ke keranjang</a>
            </div>
        </div>
    </div>
</x-layout>
