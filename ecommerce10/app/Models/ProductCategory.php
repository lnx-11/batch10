<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }
}
