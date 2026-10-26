<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Panel Pengelolaan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold">Selamat datang di panel LИX.goods</h3>
                    <p class="mt-2 text-sm text-gray-600">Kelola katalog dan kategori toko dari halaman ini.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('dashboard.products.index') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Kelola Produk</a>
                        <a href="{{ route('dashboard.product-categories.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Kelola Kategori</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
