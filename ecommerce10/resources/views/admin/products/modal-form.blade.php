<div class="admin-modal-heading">
    <h2 id="admin-modal-title" class="text-lg font-semibold">Edit Produk</h2>
    <button type="button" class="admin-modal-close" data-admin-modal-close aria-label="Tutup">&times;</button>
</div>
<form method="POST" action="{{ route('dashboard.products.update', $product) }}" enctype="multipart/form-data" class="mt-5 space-y-4" data-admin-modal-form data-product-image-form data-image-required="false">
    @csrf
    @method('PUT')
    <div class="hidden rounded-md bg-red-50 p-3 text-sm text-red-700" data-form-errors role="alert"></div>
    <div>
        <x-input-label for="modal_product_name" value="Nama produk" />
        <x-text-input id="modal_product_name" name="name" type="text" maxlength="255" class="mt-1 block w-full" :value="$product->name" required data-modal-field="name" />
        <p class="mt-1 text-sm text-red-600" data-field-error="name"></p>
    </div>
    <div>
        <x-input-label for="modal_product_category_id" value="Kategori" />
        <div class="mt-1 flex gap-2">
            <select id="modal_product_category_id" name="product_category_id" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" data-modal-field="product_category_id" data-product-category-select>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($product->product_category_id == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="button" class="shrink-0 rounded-md border border-indigo-600 px-3 text-sm font-semibold text-indigo-600 hover:bg-indigo-50" data-admin-modal-trigger data-modal-success="category-option" data-modal-url="{{ route('dashboard.product-categories.create') }}" aria-label="Tambah kategori baru">+ Kategori</button>
        </div>
        <p class="mt-1 text-sm text-red-600" data-field-error="product_category_id"></p>
    </div>
    <div>
        <x-input-label for="modal_product_description" value="Deskripsi" />
        <textarea id="modal_product_description" name="description" rows="3" maxlength="5000" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" data-modal-field="description">{{ $product->description }}</textarea>
        <p class="mt-1 text-sm text-red-600" data-field-error="description"></p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="modal_product_stock" value="Stok" />
            <x-text-input id="modal_product_stock" name="stock" type="number" min="0" step="1" class="mt-1 block w-full" :value="$product->stock" required data-modal-field="stock" />
            <p class="mt-1 text-sm text-red-600" data-field-error="stock"></p>
        </div>
        <div>
            <x-input-label for="modal_product_price" value="Harga (Rp)" />
            <x-text-input id="modal_product_price" name="price" type="number" min="0" step="1" class="mt-1 block w-full" :value="$product->price" required data-modal-field="price" />
            <p class="mt-1 text-sm text-red-600" data-field-error="price"></p>
        </div>
    </div>
    <div>
        <x-input-label for="modal_product_image" value="Gambar produk" />
        <img src="{{ asset($product->image) }}" alt="{{ $product->name }}" class="my-2 h-20 w-20 rounded-md object-cover" data-current-image>
        <input id="modal_product_image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-semibold file:text-indigo-700 dark:text-gray-300" data-image-source>
        <input type="file" name="image" class="hidden" tabindex="-1" aria-hidden="true" data-cropped-image>
        <p class="mt-1 text-xs text-gray-500">Gambar pengganti opsional. Potong JPG, PNG, atau WEBP persegi (minimal 100 × 100 piksel, maksimal 2 MB).</p>
        <div class="mt-3 hidden max-w-xl overflow-hidden rounded-md bg-gray-100" data-cropper-container>
            <img src="" alt="Pratinjau gambar untuk dipotong" class="block max-h-64 w-full" data-cropper-image>
        </div>
        <button type="button" class="mt-2 hidden rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700" data-crop-image>Gunakan hasil potongan</button>
        <div class="mt-2 hidden items-center gap-3" data-cropped-preview-container>
            <img src="" alt="Pratinjau hasil potongan gambar" class="h-16 w-16 rounded-md object-cover" data-cropped-preview>
            <span class="text-sm text-green-700">Gambar siap disimpan.</span>
        </div>
        <p class="mt-1 text-sm text-red-600" role="status" aria-live="polite" data-crop-status></p>
        <p class="mt-1 text-sm text-red-600" data-field-error="image"></p>
    </div>
    <div class="flex justify-end gap-3">
        <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50" data-admin-modal-close>Batal</button>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" data-modal-submit>Simpan Perubahan</button>
    </div>
</form>
