<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Headphone Studio One', 'category' => 'Teknologi', 'description' => 'Suara detail, nyaman dipakai seharian.', 'price' => 649000, 'stock' => 15, 'image' => 'images/products/headphone-studio.jpg'],
            ['name' => 'Lampu Meja Arc', 'category' => 'Rumah', 'description' => 'Cahaya hangat untuk sudut yang tenang.', 'price' => 429000, 'stock' => 20, 'image' => 'images/products/lampu-meja.jpg'],
            ['name' => 'Jam Tangan Field', 'category' => 'Gaya hidup', 'description' => 'Desain sederhana, siap ikut ke mana saja.', 'price' => 789000, 'stock' => 10, 'image' => 'images/products/jam-tangan.jpg'],
            ['name' => 'Sneaker Everyday', 'category' => 'Gaya hidup', 'description' => 'Ringan untuk langkah panjang dan santai.', 'price' => 579000, 'stock' => 25, 'image' => 'images/products/sneaker.jpg'],
            ['name' => 'Tumbler Transit', 'category' => 'Gaya hidup', 'description' => 'Menjaga minuman tetap pas sepanjang hari.', 'price' => 189000, 'stock' => 50, 'image' => 'images/products/tumbler.jpg'],
            ['name' => 'Kamera Pocket 35', 'category' => 'Teknologi', 'description' => 'Momen sehari-hari, tersimpan lebih berkesan.', 'price' => 1199000, 'stock' => 8, 'image' => 'images/products/kamera-pocket.jpg'],
            ['name' => 'Vas Bentuk Sora', 'category' => 'Rumah', 'description' => 'Siluet organik untuk meja dan rak favorit.', 'price' => 249000, 'stock' => 12, 'image' => 'images/products/vas-sora.jpg'],
            ['name' => 'Tas Harian Transit', 'category' => 'Aksesori', 'description' => 'Ruang cukup untuk semua yang penting.', 'price' => 359000, 'stock' => 30, 'image' => 'images/products/tas-transit.jpg'],
        ];

        foreach ($products as $product) {
            $categoryId = DB::table('product_categories')
                ->where('name', $product['category'])
                ->value('id');

            if ($categoryId === null) {
                throw new RuntimeException("Kategori {$product['category']} belum tersedia. Jalankan ProductCategorySeeder terlebih dahulu.");
            }

            $values = [
                'product_category_id' => $categoryId,
                'slug' => Str::slug($product['name']),
                'price' => $product['price'],
                'description' => $product['description'],
                'stock' => $product['stock'],
                'image' => $product['image'],
                'updated_at' => now(),
            ];

            $existingProduct = DB::table('products')->where('name', $product['name']);

            if ($existingProduct->exists()) {
                $existingProduct->update($values);
            } else {
                DB::table('products')->insert([
                    ...$values,
                    'name' => $product['name'],
                    'created_at' => now(),
                ]);
            }
        }
    }
}
