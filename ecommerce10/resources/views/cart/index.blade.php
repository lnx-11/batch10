<x-layout title="Keranjang - LИX.goods">
    <section class="page-heading pb-4">
        <span class="eyebrow">Ringkasan belanja</span>
        <h1 class="mt-2 mb-2">Keranjangmu.</h1>
        <p class="text-secondary mb-0">Periksa kembali pilihan sebelum melanjutkan.</p>
    </section>

    @if (session('success'))
        <div class="alert alert-success rounded-3" role="status">{{ session('success') }}</div>
    @endif

    @if (empty($items))
        <div class="empty-cart">
            <div class="benefit-icon mx-auto mb-3" style="width:58px;height:58px;font-size:1.5rem">&#9825;</div>
            <h2 class="h4 fw-bold">Keranjangmu masih kosong</h2>
            <p class="text-secondary">Yuk, cari produk yang kamu suka.</p>
            <a href="{{ url('products') }}" class="btn btn-brand mt-2">Jelajahi produk <span aria-hidden="true">&rarr;</span></a>
        </div>
    @else
        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="cart-panel p-0 overflow-hidden">
                    <div class="p-4 border-bottom"><h2 class="h5 fw-bold mb-1">Produk pilihanmu</h2><span class="small text-secondary">{{ count($items) }} produk di keranjang</span></div>
                    <div class="table-responsive px-2 px-md-3">
                        <table class="table cart-table mb-0">
                            <thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th class="text-end">Subtotal</th><th><span class="visually-hidden">Aksi</span></th></tr></thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td class="fw-bold">{{ $item['name'] }}</td>
                                        <td class="text-secondary">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <form action="{{ route('cart.quantity', $item['id']) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="delta" value="-1">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Kurangi jumlah {{ $item['name'] }}">&minus;</button>
                                                </form>
                                                <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ $item['qty'] }}</span>
                                                <form action="{{ route('cart.quantity', $item['id']) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="delta" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" aria-label="Tambah jumlah {{ $item['name'] }}">+</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Hapus {{ $item['name'] }} dari keranjang">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="cart-panel">
                    <h2 class="h5 fw-bold mb-4">Ringkasan pesanan</h2>
                    <div class="d-flex justify-content-between text-secondary mb-3"><span>Total item</span><span>{{ collect($items)->sum('qty') }} item</span></div>
                    <div class="cart-total d-flex justify-content-between align-items-center mb-4"><span class="fw-bold">Total harga</span><span class="product-price">Rp {{ number_format(collect($items)->sum(fn ($i) => $i['price'] * $i['qty']), 0, ',', '.') }}</span></div>
                    <a href="{{ url('checkout') }}" class="btn btn-brand w-100">Lanjut checkout <span aria-hidden="true">&rarr;</span></a>
                    <a href="{{ url('products') }}" class="btn btn-outline-brand w-100 mt-2">Lanjut belanja</a>
                </div>
            </div>
        </div>
    @endif
</x-layout>