<x-layout title="Katalog - LИX.goods">
    <section class="page-heading">
        <span class="eyebrow">Koleksi pilihan</span>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mt-2">
            <div><h1 class="mb-2">Cari favoritmu.</h1><p class="text-secondary mb-0">Pilihan praktis untuk melengkapi keseharianmu.</p></div>
            <div class="catalog-heading-controls d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                <form method="GET" action="{{ route('products.index') }}" class="catalog-search-form" role="search" data-catalog-search>
                    <label for="catalog-search" class="visually-hidden">Cari produk</label>
                    <div class="input-group input-group-sm">
                        <input
                            id="catalog-search"
                            name="search"
                            type="search"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Cari produk..."
                            autocomplete="off"
                            aria-label="Cari berdasarkan nama atau deskripsi produk"
                        >
                        <button type="submit" class="btn btn-brand">Cari</button>
                    </div>
                </form>
                <span class="category-chip active text-nowrap">{{ $products->total() }} produk</span>
            </div>
        </div>
    </section>
    <style>
        .catalog-heading-controls { width: 100%; }
        .catalog-search-form { width: 100%; }
        @media (min-width: 576px) {
            .catalog-search-form { width: min(320px, 38vw); }
        }
        @media (min-width: 768px) {
            .catalog-heading-controls { width: auto; }
            .catalog-search-form { flex: 0 1 320px; }
        }
    </style>
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
    <script>
        (() => {
            const form = document.querySelector('[data-catalog-search]');
            const input = form?.querySelector('input[name="search"]');
            const focusStateKey = 'catalog-search-focus';

            if (!form || !input) return;

            const savedFocusState = sessionStorage.getItem(focusStateKey);

            if (savedFocusState !== null) {
                sessionStorage.removeItem(focusStateKey);

                try {
                    const selectionStart = Math.min(Number(savedFocusState), input.value.length);
                    input.focus();
                    input.setSelectionRange(selectionStart, selectionStart);
                } catch (error) {
                    console.error('Posisi kursor pencarian gagal dipulihkan.', error);
                }
            }

            let timeout;
            input.addEventListener('input', () => {
                window.clearTimeout(timeout);
                timeout = window.setTimeout(() => {
                    sessionStorage.setItem(focusStateKey, input.selectionStart ?? input.value.length);
                    form.requestSubmit();
                }, 400);
            });
        })();
    </script>
</x-layout>