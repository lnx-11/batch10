<x-layout title="Katalog - LИX.goods">
    <section class="page-heading">
        <span class="eyebrow">Koleksi pilihan</span>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mt-2">
            <div><h1 class="mb-2">Cari favoritmu.</h1><p class="text-secondary mb-0">Pilihan praktis untuk melengkapi keseharianmu.</p></div>
            <span class="category-chip active">{{ $products->total() }} produk</span>
        </div>
    </section>
    <div id="cart-feedback" class="alert alert-success d-none" role="status"></div>
    @if (session('success'))
        <div class="alert alert-success rounded-3" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger rounded-3" role="alert">{{ session('error') }}</div>
    @endif
    <div class="row g-4 mt-1">
        @forelse ($products as $product)
            <div class="col-sm-6 col-lg-4">
                <x-product-card
                    :image="asset($product->image)"
                    :product-name="$product->name"
                    :product-description="$product->description"
                    :price="$product->price"
                    :stock="$product->stock"
                    :category="$product->category->name"
                    :product-id="$product->id"
                />
            </div>
        @empty
            <div class="col-12"><p class="empty-cart">Belum ada produk untuk ditampilkan.</p></div>
        @endforelse
    </div>
    <div class="mt-5 pt-2 d-flex justify-content-end catalog-pagination">
        {{ $products->links('pagination::bootstrap-5') }}
    </div>
</x-layout>