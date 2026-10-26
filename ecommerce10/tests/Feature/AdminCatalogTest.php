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
    $this->actingAs(User::factory()->create());
});

test('admin can view product and category tables', function () {
    $this->get(route('dashboard.products.index'))
        ->assertOk()
        ->assertSee('Headphone Studio One')
        ->assertSee('Deskripsi')
        ->assertSee('Stok')
        ->assertSee('Harga')
        ->assertSee('Gambar');

    $this->get(route('dashboard.product-categories.index'))
        ->assertOk()
        ->assertSee('Teknologi')
        ->assertSee('Jumlah Produk')
        ->assertSee('2');
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

test('a category with products cannot be deleted', function () {
    $category = ProductCategory::where('slug', 'teknologi')->firstOrFail();

    $this->from(route('dashboard.product-categories.index'))
        ->delete(route('dashboard.product-categories.destroy', $category))
        ->assertRedirect(route('dashboard.product-categories.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('product_categories', ['id' => $category->id]);
});

test('admin can create a product with an image', function () {
    $image = UploadedFile::fake()->create('new-product.png', 1, 'image/png');
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

test('admin can update and delete a product', function () {
    $product = Product::where('slug', 'headphone-studio-one')->firstOrFail();

    $this->get(route('dashboard.products.edit', $product))->assertOk();

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
