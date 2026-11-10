<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Daftar Produk</h2>
            <a href="{{ route('dashboard.products.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Tambah Produk
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('dashboard.products.index') }}" x-data class="flex flex-col gap-3 rounded-lg bg-white p-4 shadow-sm md:flex-row md:items-end dark:bg-gray-800">
                <div class="min-w-0 flex-1">
                    <x-input-label for="search" value="Cari produk" class="text-xs" />
                    <x-text-input id="search" name="search" type="search" class="mt-1 block w-full py-2 text-sm" :value="$search" placeholder="Nama atau deskripsi" x-on:input.debounce.400ms="$el.form.requestSubmit()" />
                </div>
                <div class="w-full md:w-52">
                    <x-input-label for="category_id" value="Kategori" class="text-xs" />
                    <select id="category_id" name="category_id" x-on:change="$el.form.requestSubmit()" class="mt-1 block w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-44">
                    <x-input-label for="sort" value="Urutkan" class="text-xs" />
                    <select id="sort" name="sort" x-on:change="$el.form.requestSubmit()" class="mt-1 block w-full rounded-md border-gray-300 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="name_asc" @selected($sort === 'name_asc')>Nama A-Z</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>Nama Z-A</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>Harga terendah</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>Harga tertinggi</option>
                    </select>
                </div>
                <a href="{{ route('dashboard.products.index') }}" class="admin-reset-button">Reset</a>
            </form>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Gambar</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Deskripsi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Stok</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Harga</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $product->id }}</td>
                                    <td class="px-4 py-3">
                                        @if ($product->image)
                                            <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="h-14 w-14 rounded-md object-cover">
                                        @else
                                            <span class="text-sm text-gray-500">Tidak ada gambar</span>
                                        @endif
                                    </td>
                                    <td class="min-w-40 px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $product->name }}
                                        <div class="text-xs font-normal text-gray-500">{{ $product->category->name }}</div>
                                    </td>
                                    <td class="min-w-56 px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($product->description, 100) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $product->stock }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-200">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('dashboard.products.edit', $product) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800" data-admin-modal-trigger>Edit</a>
                                            <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Produk tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $products->links() }}
        </div>
    </div>

    <dialog class="admin-modal-dialog" data-admin-modal aria-labelledby="admin-modal-title">
        <div class="admin-modal-panel" data-admin-modal-content></div>
    </dialog>
</x-app-layout>
