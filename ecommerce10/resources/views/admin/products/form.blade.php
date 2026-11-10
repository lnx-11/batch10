<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ $product->exists ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}" enctype="multipart/form-data" class="space-y-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800" data-product-image-form data-image-required="{{ $product->exists ? 'false' : 'true' }}">
                @csrf
                @if ($product->exists)
                    @method('PUT')
                @endif

                <div>
                    <x-input-label for="name" value="Nama produk" />
                    <x-text-input id="name" name="name" type="text" maxlength="255" class="mt-1 block w-full" :value="old('name', $product->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="product_category_id" value="Kategori" />
                    <div class="mt-1 flex gap-2">
                        <select id="product_category_id" name="product_category_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" data-product-category-select>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('product_category_id', $product->product_category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" class="shrink-0 rounded-md border border-indigo-600 px-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-50" data-admin-modal-trigger data-modal-success="category-option" data-modal-url="{{ route('dashboard.product-categories.create') }}" aria-label="Tambah kategori baru">+ Kategori</button>
                    </div>
                    <x-input-error :messages="$errors->get('product_category_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Deskripsi" />
                    <textarea id="description" name="description" rows="4" maxlength="5000" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ old('description', $product->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="stock" value="Stok" />
                        <x-text-input id="stock" name="stock" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('stock', $product->stock)" required />
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
                        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="my-3 h-24 w-24 rounded-md object-cover" data-current-image>
                    @endif
                    <input id="image" type="file" accept="image/jpeg,image/png,image/webp" @required(!$product->exists) class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300" data-image-source>
                    <input type="file" name="image" class="hidden" tabindex="-1" aria-hidden="true" data-cropped-image>
                    <p class="mt-1 text-sm text-gray-500">Pilih gambar JPG, PNG, atau WEBP. Potong gambar sebelum disimpan; hasil potongan berbentuk persegi, minimal 100 × 100 piksel, dan maksimal 2 MB.</p>
                    <div class="mt-4 hidden max-w-xl overflow-hidden rounded-md bg-gray-100 dark:bg-gray-900" data-cropper-container>
                        <img src="" alt="Pratinjau gambar untuk dipotong" class="block max-h-[28rem] w-full" data-cropper-image>
                    </div>
                    <button type="button" class="mt-3 hidden rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" data-crop-image>
                        Gunakan hasil potongan
                    </button>
                    <div class="mt-3 hidden items-center gap-3" data-cropped-preview-container>
                        <img src="" alt="Pratinjau hasil potongan gambar" class="h-24 w-24 rounded-md object-cover" data-cropped-preview>
                        <span class="text-sm font-medium text-green-700 dark:text-green-400">Gambar siap disimpan.</span>
                    </div>
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400" role="status" aria-live="polite" data-crop-status></p>
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $product->exists ? 'Simpan Perubahan' : 'Tambah Produk' }}</x-primary-button>
                    <a href="{{ route('dashboard.products.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Batal</a>
                </div>
            </form>
        </div>
    </div>
    <dialog class="admin-modal-dialog" data-admin-modal aria-labelledby="admin-modal-title">
        <div class="admin-modal-panel" data-admin-modal-content></div>
    </dialog>
</x-app-layout>
