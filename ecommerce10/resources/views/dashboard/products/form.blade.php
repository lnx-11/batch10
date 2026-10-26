<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $product->exists ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}" enctype="multipart/form-data" class="space-y-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                @csrf
                @if ($product->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="name" value="Nama produk" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="product_category_id" value="Kategori" />
                    <select id="product_category_id" name="product_category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('product_category_id', $product->product_category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('product_category_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Deskripsi" />
                    <textarea id="description" name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('description', $product->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="stock" value="Stok" />
                        <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', $product->stock)" required />
                        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="price" value="Harga (Rp)" />
                        <x-text-input id="price" name="price" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('price', $product->price)" required />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="image" value="Gambar produk" />
                    @if ($product->image)
                        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="my-3 h-24 w-24 rounded-md object-cover">
                    @endif
                    <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" @required(!$product->exists) class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300">
                    <p class="mt-1 text-sm text-gray-500">Format JPG, PNG, atau WEBP; maksimal 2 MB. Kosongkan jika tidak mengganti gambar.</p>
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $product->exists ? 'Simpan Perubahan' : 'Tambah Produk' }}</x-primary-button>
                    <a href="{{ route('dashboard.products.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
