<x-layout title="LИX.goods | Toko Pilihan">
    <section class="hero p-4 p-md-5 mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 hero-content py-lg-4">
                <span class="trust-pill mb-4"><span aria-hidden="true">&#10022;</span> OBJEK PILIHAN, HARI-HARI LEBIH BAIK</span>
                <h1>Temukan yang kamu suka. <span class="text-warning">Bawa pulang.</span></h1>
                <p class="hero-copy mt-4">Koleksi pilihan untuk menemani aktivitas dan gaya kamu setiap hari. Cari favoritmu dan mulai belanja dengan nyaman.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="{{ route('products.index') }}" class="btn btn-light btn-lg fw-bold rounded-3 px-4">Jelajahi Produk <span aria-hidden="true">&rarr;</span></a>
                    <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="btn btn-outline-light btn-lg rounded-3 px-4">{{ auth()->check() ? 'Lihat Keranjang' : 'Login untuk keranjang' }}</a>
                </div>
                <div class="d-flex flex-wrap gap-4 mt-4 pt-2 small text-white-50">
                    <span>&#10003; Produk pilihan</span><span>&#10003; Belanja praktis</span><span>&#10003; Nyaman setiap saat</span>
                </div>
            </div>
            <div class="col-lg-5 hero-visual">
                <div class="hero-glow"></div>
                <img class="hero-product" src="{{ asset('images/hero-workspace.jpg') }}" alt="Ruang kerja dengan koleksi produk pilihan">
            </div>
        </div>
    </section>

    <section class="mb-5" id="koleksi">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-3">
            <div><span class="eyebrow">LИX.goods pilihan</span><h2 class="section-title mt-2 mb-0">Favorit untuk hari-harimu.</h2></div>
            <a href="{{ auth()->check() ? route('products.index') : route('login') }}" class="btn btn-outline-brand">Lihat semua produk <span aria-hidden="true">&rarr;</span></a>
        </div>
        <div id="cart-feedback" class="alert alert-success d-none" role="status"></div>
        @if (session('success'))
            <div class="alert alert-success rounded-3" role="status">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger rounded-3" role="alert">{{ session('error') }}</div>
        @endif
        <div class="row g-4 mt-1">
            @foreach ($products as $product)
                <div class="col-sm-6 col-lg-3">
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
            @endforeach
        </div>
    </section>

    <section class="mb-5">
        <div class="row g-3">
            <div class="col-md-4"><div class="benefit-card d-flex gap-3"><div class="benefit-icon">01</div><div><h2 class="h6 fw-bold mb-1">Pilihan terkurasi</h2><p class="small text-secondary mb-0">Temukan barang yang cocok untuk kebutuhanmu.</p></div></div></div>
            <div class="col-md-4"><div class="benefit-card d-flex gap-3"><div class="benefit-icon">02</div><div><h2 class="h6 fw-bold mb-1">Mudah dijelajahi</h2><p class="small text-secondary mb-0">Cari produk dan lihat detailnya dengan cepat.</p></div></div></div>
            <div class="col-md-4"><div class="benefit-card d-flex gap-3"><div class="benefit-icon">03</div><div><h2 class="h6 fw-bold mb-1">Belanja nyaman</h2><p class="small text-secondary mb-0">Simpan pilihanmu di halaman keranjang.</p></div></div></div>
        </div>
    </section>

    <section class="rounded-4 p-4 p-md-5" style="background:#eeedff">
        <div class="row align-items-center g-3">
            <div class="col-md-8"><span class="eyebrow">Saatnya menemukan favorit</span><h2 class="section-title mt-2 mb-2">Ada yang menarik untukmu.</h2><p class="text-secondary mb-0">Lihat koleksi produk dan temukan pilihan yang pas.</p></div>
            <div class="col-md-4 text-md-end"><a href="{{ auth()->check() ? route('products.index') : route('login') }}" class="btn btn-brand">Lihat semua produk <span aria-hidden="true">&rarr;</span></a></div>
        </div>
    </section>
</x-layout>