<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductCard extends Component
{
    public function __construct(
        public string $image,
        public string $productName,
        public string $productDescription,
        public int $price,
        public int $stock,
        public string $category,
        public int $productId,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.product-card');
    }
}
