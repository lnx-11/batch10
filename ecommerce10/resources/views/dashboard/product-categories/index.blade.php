<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Daftar Kategori Produk</h2>
            <a href="{{ route('dashboard.product-categories.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Tambah Kategori
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

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Nama Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Jumlah Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-600 dark:text-gray-200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $category->id }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $category->name }}
                                        <div class="text-xs font-normal text-gray-500">{{ $category->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $category->products_count }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('dashboard.product-categories.edit', $category) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</a>
                                            <form method="POST" action="{{ route('dashboard.product-categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $categories->links() }}
        </div>
    </div>
</x-app-layout>
