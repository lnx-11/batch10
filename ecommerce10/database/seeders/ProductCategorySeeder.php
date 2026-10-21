<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $categories = array_map(
            static fn (string $name): array => [
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['Teknologi', 'Rumah', 'Gaya hidup', 'Aksesori'],
        );

        DB::table('product_categories')->upsert($categories, ['name'], ['slug', 'updated_at']);
    }
}
