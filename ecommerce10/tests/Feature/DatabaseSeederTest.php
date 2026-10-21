<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('database seeder creates the assignment categories and eight products with unique slugs', function () {
    $this->seed();

    expect(DB::table('product_categories')->count())->toBe(4)
        ->and(DB::table('products')->count())->toBe(8)
        ->and(Schema::hasColumns('product_categories', ['name', 'slug']))->toBeTrue()
        ->and(Schema::hasColumns('products', [
            'product_category_id',
            'name',
            'slug',
            'price',
            'description',
            'stock',
            'image',
        ]))->toBeTrue()
        ->and(DB::table('product_categories')->whereNull('slug')->exists())->toBeFalse()
        ->and(DB::table('products')->whereNull('slug')->exists())->toBeFalse()
        ->and(DB::table('products')->where('price', '<=', 0)->exists())->toBeFalse()
        ->and(DB::table('products')->whereNull('product_category_id')->exists())->toBeFalse()
        ->and(DB::table('product_categories')->distinct()->count('slug'))->toBe(4)
        ->and(DB::table('products')->distinct()->count('slug'))->toBe(8);
});
