<nav x-data="{ open: false }" class="admin-navigation">
    <div class="admin-nav-inner">
        <a href="{{ route('home') }}" class="admin-brand" aria-label="{{ config('app.name', 'LИX.goods') }}">
            <x-application-logo />
            <span>{{ config('app.name', 'LИX.goods') }}</span>
        </a>

        <div class="admin-nav-links">
            <a href="{{ route('dashboard') }}" @class(['admin-nav-link', 'active' => request()->routeIs('dashboard')])>Dashboard</a>
            <a href="{{ route('dashboard.products.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('dashboard.products.*')])>Produk</a>
            <a href="{{ route('dashboard.product-categories.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('dashboard.product-categories.*')])>Kategori</a>
        </div>

        <div class="admin-nav-actions">
            <span class="admin-user-name">{{ Auth::user()->name }}</span>
            <a href="{{ route('profile.edit') }}" class="admin-nav-action">Profil</a>
            <form method="POST" action="{{ route('logout') }}" class="admin-logout">
                @csrf
                <button type="submit" class="admin-nav-action">Keluar</button>
            </form>
        </div>

        <button type="button" class="admin-nav-toggle" x-on:click="open = !open" x-bind:aria-expanded="open.toString()" aria-label="Buka navigasi">
            <svg class="h-6 w-6" x-show="!open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg class="h-6 w-6" x-show="open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <div x-cloak x-show="open" x-transition class="admin-mobile-menu">
        <a href="{{ route('dashboard') }}" @class(['admin-nav-link', 'active' => request()->routeIs('dashboard')])>Dashboard</a>
        <a href="{{ route('dashboard.products.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('dashboard.products.*')])>Produk</a>
        <a href="{{ route('dashboard.product-categories.index') }}" @class(['admin-nav-link', 'active' => request()->routeIs('dashboard.product-categories.*')])>Kategori</a>
        <div class="admin-mobile-user">{{ Auth::user()->name }}<br>{{ Auth::user()->email }}</div>
        <a href="{{ route('profile.edit') }}" class="admin-nav-link">Profil</a>
        <form method="POST" action="{{ route('logout') }}" class="admin-logout">
            @csrf
            <button type="submit" class="admin-nav-link">Keluar</button>
        </form>
    </div>
</nav>
