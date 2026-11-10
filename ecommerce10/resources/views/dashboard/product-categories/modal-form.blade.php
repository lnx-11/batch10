<div class="admin-modal-heading">
    <h2 id="admin-modal-title" class="text-lg font-semibold">{{ $category->exists ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
    <button type="button" class="admin-modal-close" data-admin-modal-close aria-label="Tutup">&times;</button>
</div>
<form method="POST" action="{{ $category->exists ? route('dashboard.product-categories.update', $category) : route('dashboard.product-categories.store') }}" class="mt-5 space-y-5" data-admin-modal-form>
    @csrf
    @if ($category->exists)
        @method('PUT')
    @endif
    <div class="hidden rounded-md bg-red-50 p-3 text-sm text-red-700" data-form-errors role="alert"></div>
    <div>
        <x-input-label for="modal_category_name" value="Nama kategori" />
        <x-text-input id="modal_category_name" name="name" type="text" maxlength="255" class="mt-1 block w-full" :value="$category->name" required autofocus data-modal-field="name" />
        <p class="mt-1 text-sm text-red-600" data-field-error="name"></p>
    </div>
    <div class="flex justify-end gap-3">
        <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50" data-admin-modal-close>Batal</button>
        <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700" data-modal-submit>{{ $category->exists ? 'Simpan Perubahan' : 'Tambah Kategori' }}</button>
    </div>
</form>
