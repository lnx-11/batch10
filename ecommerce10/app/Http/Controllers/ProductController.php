<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function home(): View
    {
        $products = Product::query()
            ->with('category')
            ->available()
            ->orderBy('id')
            ->limit(4)
            ->get();

        return view('home.index', compact('products'));
    }

    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->orderBy('id')
            ->paginate(6)
            ->withQueryString();

        return view('product.description', compact('products'));
    }
}
