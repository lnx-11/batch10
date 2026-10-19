<nav class="navbar navbar-expand-lg site-nav sticky-top">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center me-4" href="{{ url('/') }}" aria-label="LNX.goods, ke beranda">
            <img class="brand-logo" src="{{ asset('images/brand-logo.jpg') }}" alt=""><span class="brand-name">LИX<span class="text-primary">.goods</span></span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Beranda</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('products*') ? 'active' : '' }}" href="{{ url('products') }}">Produk</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('cart*') ? 'active' : '' }}" href="{{ url('cart') }}">Keranjang <span class="badge rounded-pill text-bg-primary ms-1" data-cart-count>{{ array_sum(session('cart', [])) }}</span></a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-brand px-4 py-2" href="{{ url('products') }}">Belanja sekarang <span aria-hidden="true">&rarr;</span></a></li>
            </ul>
        </div>
    </div>
</nav>
