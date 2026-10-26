<x-guest-layout>
    <header class="mb-5">
        <h1 class="auth-title">Masuk ke akunmu</h1>
        <p class="auth-description">Masuk untuk melanjutkan belanja di LИX.goods.</p>
    </header>

    <x-auth-session-status class="auth-status mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="email" value="Email" class="auth-label" />
            <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="mb-4">
            <x-input-label for="password" value="Kata sandi" class="auth-label" />
            <x-password-input id="password" class="auth-input" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <div class="flex items-center justify-between gap-3 mb-5">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                <input id="remember_me" type="checkbox" class="auth-checkbox rounded border-gray-300 shadow-sm" name="remember">
                <span>Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-primary-button class="auth-button w-full">
            Masuk
        </x-primary-button>
    </form>

    <hr class="auth-divider">
    <p class="auth-footer">Belum punya akun? <a class="auth-link" href="{{ route('register') }}">Daftar sekarang</a></p>
</x-guest-layout>
