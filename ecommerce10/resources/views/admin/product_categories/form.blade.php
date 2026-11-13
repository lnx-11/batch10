<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ $category->exists ? 'Edit Kategori' : 'Tambah Kategori' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $category->exists ? route('product-category.update', $category) : route('product-category.store') }}" class="space-y-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                @csrf
                @if ($category->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="name" value="Nama kategori" />
                    <x-text-input id="name" name="name" type="text" maxlength="255" class="mt-1 block w-full" :value="old('name', $category->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('dashboard.product-categories.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:text-gray-300">Batal</a>
                    <x-primary-button>{{ $category->exists ? 'Simpan Perubahan' : 'Tambah Kategori' }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
