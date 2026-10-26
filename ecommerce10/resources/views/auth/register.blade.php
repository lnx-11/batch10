<x-guest-layout>
    <header class="mb-5">
        <h1 class="auth-title">Buat akun baru</h1>
        <p class="auth-description">Daftar untuk mulai berbelanja di LИX.goods.</p>
    </header>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="name" value="Nama lengkap" class="auth-label" />
            <x-text-input id="name" class="auth-input" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="auth-error" />
        </div>

        <div class="mb-4">
            <x-input-label for="email" value="Email" class="auth-label" />
            <x-text-input id="email" class="auth-input" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <div class="mb-4">
            <x-input-label for="password" value="Kata sandi" class="auth-label" />
            <x-password-input id="password" class="auth-input" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <div class="mb-5">
            <x-input-label for="password_confirmation" value="Konfirmasi kata sandi" class="auth-label" />
            <x-password-input id="password_confirmation" class="auth-input" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
        </div>

        <x-primary-button class="auth-button w-full">
            Daftar
        </x-primary-button>
    </form>

    <hr class="auth-divider">
    <p class="auth-footer">Sudah punya akun? <a class="auth-link" href="{{ route('login') }}">Masuk</a></p>
</x-guest-layout>
