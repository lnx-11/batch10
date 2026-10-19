<x-layout title="Katalog - LИX.goods">
    <section class="page-heading">
        <span class="eyebrow">Koleksi pilihan</span>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mt-2">
            <div><h1 class="mb-2">Cari favoritmu.</h1><p class="text-secondary mb-0">Pilihan praktis untuk melengkapi keseharianmu.</p></div>
            <span class="category-chip active">Semua produk <span class="ms-1">{{ count($products) }}</span></span>
        </div>
    </section>
    <div id="cart-feedback" class="alert alert-success d-none" role="status"></div>
    <div class="row g-4 mt-1">
        @foreach ($products as $product)
            <div class="col-sm-6 col-lg-4">
                <x-product-card
                    :image="$product['image']"
                    :product-name="$product['name']"
                    :product-description="$product['description']"
                    :price="$product['price']"
                    :category="$product['category']"
                    :product-id="$product['id']"
                />
            </div>
        @endforeach
    </div>
</x-layout>