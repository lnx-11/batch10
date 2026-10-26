<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ProductCategory::query()
            ->withCount('products')
            ->orderBy('id')
            ->paginate(10);

        return view('dashboard.product-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $category = new ProductCategory;

        return view('dashboard.product-categories.form', compact('category'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name'],
        ]);
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        ProductCategory::query()->create($validated);

        return to_route('dashboard.product-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(ProductCategory $productCategory): View
    {
        $category = $productCategory;

        return view('dashboard.product-categories.form', compact('category'));
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:product_categories,name,'.$productCategory->id],
        ]);
        $validated['slug'] = $this->uniqueSlug($validated['name'], $productCategory->id);
        $productCategory->update($validated);

        return to_route('dashboard.product-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $productCategory->delete();

        return to_route('dashboard.product-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $suffix = 2;

        while (ProductCategory::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
