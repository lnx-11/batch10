@if ($layout === 'storefront')
    <x-layout title="Profil Saya - LИX.goods">
        <section class="page-heading pb-4">
            <span class="eyebrow">Akun pelanggan</span>
            <h1 class="mt-2 mb-2">Profil saya.</h1>
            <p class="text-secondary mb-0">Kelola informasi akun dan lihat aktivitas belanjamu.</p>
        </section>

        @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
            <div class="alert alert-success rounded-3" role="status">Perubahan berhasil disimpan.</div>
        @endif

        <section class="cart-panel mb-4" aria-labelledby="account-summary-title">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                <div>
                    <span class="eyebrow">Ringkasan akun</span>
                    <h2 id="account-summary-title" class="h4 fw-bold mt-2 mb-1">{{ $user->name }}</h2>
                    <p class="text-secondary mb-0">{{ $user->email }}</p>
                </div>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-brand">Lihat riwayat pembelian</a>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-sm-6 col-lg-4">
                    <div class="account-detail">
                        <span>Tipe akun</span>
                        <strong>{{ ucfirst($user->role ?? 'user') }}</strong>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="account-detail">
                        <span>Bergabung sejak</span>
                        <strong>{{ $user->created_at->format('d M Y') }}</strong>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="account-detail">
                        <span>Item pesanan</span>
                        <strong>{{ $user->orders_count }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <section class="cart-panel mb-4">
                    <h2 class="h5 fw-bold mb-1">Informasi pribadi</h2>
                    <p class="small text-secondary mb-4">Perbarui nama atau email yang terhubung ke akunmu.</p>
                    <form method="POST" action="{{ route('profile.update') }}" class="vstack gap-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="name" class="form-label fw-semibold">Nama lengkap</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" autocomplete="name" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div><button type="submit" class="btn btn-brand">Simpan perubahan</button></div>
                    </form>
                </section>

                <section class="cart-panel">
                    <h2 class="h5 fw-bold mb-1">Ubah kata sandi</h2>
                    <p class="small text-secondary mb-4">Gunakan kata sandi kuat untuk menjaga keamanan akunmu.</p>
                    <form method="POST" action="{{ route('password.update') }}" class="vstack gap-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="current_password" class="form-label fw-semibold">Kata sandi saat ini</label>
                            <input id="current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" required>
                            @error('current_password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="password" class="form-label fw-semibold">Kata sandi baru</label>
                            <input id="password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" required>
                            @error('password', 'updatePassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi kata sandi baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required>
                        </div>
                        <div><button type="submit" class="btn btn-outline-brand">Perbarui kata sandi</button></div>
                    </form>
                </section>
            </div>

            <div class="col-lg-5">
                <section class="cart-panel border-danger-subtle">
                    <h2 class="h5 fw-bold mb-1">Hapus akun</h2>
                    <p class="small text-secondary mb-4">Tindakan ini permanen dan akunmu tidak dapat dipulihkan.</p>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="vstack gap-3" onsubmit="return confirm('Yakin ingin menghapus akun secara permanen?')">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label for="delete_password" class="form-label fw-semibold">Konfirmasi dengan kata sandi</label>
                            <input id="delete_password" name="password" type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" autocomplete="current-password" required>
                            @error('password', 'userDeletion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div><button type="submit" class="btn btn-outline-danger">Hapus akun saya</button></div>
                    </form>
                </section>
            </div>
        </div>
    </x-layout>
@else
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Pengaturan Akun
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg" aria-labelledby="account-summary-title">
                <div class="max-w-3xl">
                    <h3 id="account-summary-title" class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Akun</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ringkasan akun dan aktivitas belanjamu.</p>
                    <dl class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Nama</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Email</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Tipe akun</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($user->role ?? 'user') }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Bergabung sejak</dt>
                            <dd class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d M Y') }}</dd>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900 sm:col-span-2">
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Pesanan tercatat</dt>
                            <dd class="mt-1 flex items-center justify-between gap-4 font-semibold text-gray-900 dark:text-gray-100">
                                <span>{{ $user->orders_count }} item pesanan</span>
                                <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-indigo-600 underline hover:text-indigo-800 dark:text-indigo-400">Lihat riwayat pembelian</a>
                            </dd>
                        </div>
                    </dl>
                </div>
            </section>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
@endif
