<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->actingAs(User::factory()->admin()->create());
});

test('admin can view product and category tables', function () {
    $this->get(route('dashboard.products.index'))
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('Deskripsi')
        ->assertSee('Stok')
        ->assertSee('Harga')
        ->assertSee('Gambar')
        ->assertSee('data-admin-modal-trigger', false)
        ->assertSee('Tambah Produk')
        ->assertDontSee('Tambah Kategori')
        ->assertSee('class="admin-reset-button"', false)
        ->assertSee("x-on:input.debounce.400ms=\"sessionStorage.setItem('admin-product-search-focus', String(\$el.selectionStart)); \$el.form.requestSubmit()\"", false)
        ->assertSee('x-on:change="$el.form.requestSubmit()"', false)
        ->assertDontSee('>Filter</button>', false);

    $this->get(route('dashboard.product-categories.index'))
        ->assertOk()
        ->assertSee('Teknologi')
        ->assertSee('Jumlah Produk')
        ->assertSee('2')
        ->assertSee('data-admin-modal-trigger', false)
        ->assertSee('class="admin-reset-button"', false)
        ->assertSee('x-on:input.debounce.400ms="$el.form.requestSubmit()"', false)
        ->assertDontSee('>Cari</button>', false);
});

test('admin can search filter and sort products', function () {
    $technology = ProductCategory::where('slug', 'teknologi')->firstOrFail();

    $this->get(route('dashboard.products.index', [
        'search' => 'Headphone',
        'category_id' => $technology->id,
    ]))
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertDontSee('Kamera Pocket 35')
        ->assertSee('value="Headphone"', false);

    $this->get(route('dashboard.products.index', ['category_id' => $technology->id]))
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('Kamera Pocket 35')
        ->assertDontSee('Lampu Meja Arc');

    $this->get(route('dashboard.products.index', ['sort' => 'price_asc']))
        ->assertOk()
        ->assertSeeInOrder(['Tumbler Transit', 'Vas Bentuk Sora', 'Tas Harian Transit'])
        ->assertSee('value="price_asc" selected', false);
});

test('admin can search categories by name or slug', function () {
    $this->get(route('dashboard.product-categories.index', ['search' => 'tekno']))
        ->assertOk()
        ->assertSee('Teknologi')
        ->assertDontSee('Gaya hidup')
        ->assertSee('value="tekno"', false);
});

test('admin can create update and delete a category', function () {
    $this->post(route('dashboard.product-categories.store'), ['name' => 'Peralatan'])
        ->assertRedirect(route('dashboard.product-categories.index'));

    $category = ProductCategory::where('slug', 'peralatan')->firstOrFail();
    $this->get(route('dashboard.product-categories.edit', $category))->assertOk();

    $this->put(route('dashboard.product-categories.update', $category), ['name' => 'Peralatan Rumah'])
        ->assertRedirect(route('dashboard.product-categories.index'));

    expect($category->fresh()->slug)->toBe('peralatan-rumah');

    $this->delete(route('dashboard.product-categories.destroy', $category))
        ->assertRedirect(route('dashboard.product-categories.index'));

    $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
});

test('admin can load and save category forms in a modal', function () {
    $createResponse = $this->withHeaders(['X-Admin-Modal' => 'true'])
        ->getJson(route('dashboard.product-categories.create'))
        ->assertOk()
        ->assertJsonStructure(['html']);

    expect($createResponse->json('html'))->toContain('Tambah Kategori');

    $this->postJson(route('dashboard.product-categories.store'), ['name' => 'Aksesori Baru'])
        ->assertOk()
        ->assertJsonPath('category.name', 'Aksesori Baru');

    $category = ProductCategory::where('name', 'Aksesori Baru')->firstOrFail();
    $editResponse = $this->withHeaders(['X-Admin-Modal' => 'true'])
        ->getJson(route('dashboard.product-categories.edit', $category))
        ->assertOk();

    expect($editResponse->json('html'))->toContain('Edit Kategori');

    $this->putJson(route('dashboard.product-categories.update', $category), ['name' => 'Aksesori Diperbarui'])
        ->assertOk()
        ->assertJsonPath('message', 'Kategori berhasil diperbarui.');
    expect($category->fresh()->name)->toBe('Aksesori Diperbarui');
});

test('category form rejects missing duplicate and oversized names', function () {
    $this->from(route('dashboard.product-categories.create'))
        ->post(route('dashboard.product-categories.store'), ['name' => ''])
        ->assertRedirect(route('dashboard.product-categories.create'))
        ->assertSessionHasErrors('name');

    $this->from(route('dashboard.product-categories.create'))
        ->post(route('dashboard.product-categories.store'), ['name' => 'Teknologi'])
        ->assertRedirect(route('dashboard.product-categories.create'))
        ->assertSessionHasErrors('name');

    $this->from(route('dashboard.product-categories.create'))
        ->post(route('dashboard.product-categories.store'), ['name' => str_repeat('a', 256)])
        ->assertRedirect(route('dashboard.product-categories.create'))
        ->assertSessionHasErrors('name');
});

test('a category with products cannot be deleted', function () {
    $category = ProductCategory::where('slug', 'teknologi')->firstOrFail();

    $this->from(route('dashboard.product-categories.index'))
        ->delete(route('dashboard.product-categories.destroy', $category))
        ->assertRedirect(route('dashboard.product-categories.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('product_categories', ['id' => $category->id]);
});

test('admin can create a product with an image', function () {
    $this->get(route('dashboard.products.create'))
        ->assertOk()
        ->assertSee('data-modal-success="category-option"', false)
        ->assertSee('+ Kategori');

    $image = UploadedFile::fake()->createWithContent(
        'new-product.jpg',
        File::get(public_path('images/products/headphone-studio.jpg'))
    );
    $createdImagePath = null;

    try {
        $this->post(route('dashboard.products.store'), [
            'product_category_id' => ProductCategory::where('slug', 'teknologi')->value('id'),
            'name' => 'Produk Admin Baru',
            'description' => 'Produk yang dibuat dari halaman admin.',
            'stock' => 7,
            'price' => 125000,
            'image' => $image,
        ])->assertRedirect(route('dashboard.products.index'));

        $product = Product::where('slug', 'produk-admin-baru')->firstOrFail();
        $createdImagePath = $product->image;

        expect($product->price)->toBe(125000)
            ->and($product->stock)->toBe(7)
            ->and(File::exists(public_path($product->image)))->toBeTrue();
    } finally {
        if ($createdImagePath !== null) {
            File::delete(public_path($createdImagePath));
        }
    }
});

test('product validation rejects missing fields invalid values and invalid images', function () {
    $categoryId = ProductCategory::where('slug', 'teknologi')->value('id');
    $invalidProduct = [
        'product_category_id' => $categoryId,
        'name' => '',
        'description' => str_repeat('a', 5001),
        'stock' => -1,
        'price' => -1,
    ];

    $this->from(route('dashboard.products.create'))
        ->post(route('dashboard.products.store'), $invalidProduct)
        ->assertRedirect(route('dashboard.products.create'))
        ->assertSessionHasErrors(['name', 'description', 'stock', 'price', 'image']);

    $invalidProduct['name'] = 'Produk Dengan Gambar Tidak Valid';
    $invalidProduct['description'] = 'Deskripsi produk.';
    $invalidProduct['stock'] = 1;
    $invalidProduct['price'] = 1000;
    $invalidProduct['image'] = UploadedFile::fake()->create('dokumen.pdf', 1, 'application/pdf');

    $this->from(route('dashboard.products.create'))
        ->post(route('dashboard.products.store'), $invalidProduct)
        ->assertRedirect(route('dashboard.products.create'))
        ->assertSessionHasErrors('image');
});

test('admin can update and delete a product', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->get(route('dashboard.products.edit', $product))
        ->assertOk()
        ->assertSee('data-product-image-form', false)
        ->assertSee('data-crop-image', false)
        ->assertSee('data-cropped-image', false)
        ->assertSee('Potong gambar sebelum disimpan');

    $modalResponse = $this->withHeaders(['X-Admin-Modal' => 'true'])
        ->getJson(route('dashboard.products.edit', $product))
        ->assertOk();

    expect($modalResponse->json('html'))->toContain('Edit Produk');
    expect($modalResponse->json('html'))->toContain('data-modal-success="category-option"');

    $this->put(route('dashboard.products.update', $product), [
        'product_category_id' => $product->product_category_id,
        'name' => 'Headphone Studio Updated',
        'description' => 'Deskripsi yang sudah diperbarui.',
        'stock' => 9,
        'price' => 699000,
    ])->assertRedirect(route('dashboard.products.index'));

    expect($product->fresh()->slug)->toBe('headphone-studio-updated')
        ->and($product->fresh()->price)->toBe(699000);

    $this->delete(route('dashboard.products.destroy', $product))
        ->assertRedirect(route('dashboard.products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('admin can save product changes from the edit modal', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->putJson(route('dashboard.products.update', $product), [
        'product_category_id' => $product->product_category_id,
        'name' => 'Produk Diperbarui Dari Modal',
        'description' => 'Deskripsi modal.',
        'stock' => 11,
        'price' => 735000,
    ])->assertOk()
        ->assertJsonPath('message', 'Produk berhasil diperbarui.');

    expect($product->fresh()->slug)->toBe('produk-diperbarui-dari-modal')
        ->and($product->fresh()->stock)->toBe(11)
        ->and($product->fresh()->price)->toBe(735000);
});

test('a product referenced by an order cannot be deleted', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    DB::table('orders')->insert([
        'user_id' => auth()->id(),
        'product_id' => $product->id,
        'quantity' => 1,
        'total' => $product->price,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->from(route('dashboard.products.index'))
        ->delete(route('dashboard.products.destroy', $product))
        ->assertRedirect(route('dashboard.products.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('products', ['id' => $product->id]);
});
