<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_category_id',
        'name',
        'slug',
        'price',
        'description',
        'stock',
        'image',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }
}
