<nav class="navbar navbar-expand-lg site-nav sticky-top">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center me-4" href="{{ route('home') }}" aria-label="LИX.goods, ke beranda">
            <img class="brand-logo" src="{{ asset('images/brand-logo.jpg') }}" alt=""><span class="brand-name">LИX<span class="text-primary">.goods</span></span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu" aria-controls="navbarMenu" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('products*') ? 'active' : '' }}" href="{{ url('products') }}">Produk</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->is('cart*') ? 'active' : '' }}" href="{{ auth()->check() ? route('cart.index') : route('login') }}">Keranjang <span class="badge rounded-pill text-bg-primary ms-1" data-cart-count>{{ array_sum(session('cart', [])) }}</span></a></li>
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link border-0 bg-transparent">Logout</button>
                        </form>
                    </li>
                @endguest
                <li class="nav-item ms-lg-2"><a class="btn btn-brand px-4 py-2" href="{{ auth()->check() ? route('products.index') : route('login') }}">Belanja sekarang <span aria-hidden="true">&rarr;</span></a></li>
            </ul>
        </div>
    </div>
</nav>
