<article class="product-card">
    <div class="product-image-wrap">
        <span class="product-badge">{{ $category }}</span>
        <img src="{{ $image }}" class="product-image" alt="{{ $productName }}" loading="lazy">
    </div>
    <div class="product-body">
        <h2 class="h5 product-title mb-2">{{ $productName }}</h2>
        <p class="product-description mb-3">{{ $productDescription }}</p>
        <div class="d-flex align-items-center justify-content-between gap-2">
            <span class="product-price">Rp {{ number_format($price, 0, ',', '.') }}</span>
            <form action="{{ route('cart.store', $productId) }}" method="POST" data-cart-ajax>
                @csrf
                <button type="submit" class="btn btn-brand py-2 px-3">Tambah <span aria-hidden="true">+</span></button>
            </form>
        </div>
    </div>
</article>