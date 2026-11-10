<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($filters['search'] ?? '');

        $categories = ProductCategory::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.product-categories.index', compact('categories', 'search'));
    }

    public function create(): View|JsonResponse
    {
        $category = new ProductCategory;

        if (request()->header('X-Admin-Modal') === 'true') {
            return response()->json([
                'html' => view('dashboard.product-categories.modal-form', compact('category'))->render(),
            ]);
        }

        return view('dashboard.product-categories.form', compact('category'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'name')],
        ]);
        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $category = ProductCategory::query()->create($validated);

        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Kategori berhasil ditambahkan.');

            return response()->json([
                'message' => 'Kategori berhasil ditambahkan.',
                'category' => ['id' => $category->id, 'name' => $category->name],
            ]);
        }

        return to_route('dashboard.product-categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(ProductCategory $productCategory): View|JsonResponse
    {
        $category = $productCategory;

        if (request()->header('X-Admin-Modal') === 'true') {
            return response()->json([
                'html' => view('dashboard.product-categories.modal-form', compact('category'))->render(),
            ]);
        }

        return view('dashboard.product-categories.form', compact('category'));
    }

    public function update(Request $request, ProductCategory $productCategory): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'name')->ignore($productCategory->id)],
        ]);
        $validated['slug'] = $this->uniqueSlug($validated['name'], $productCategory->id);
        $productCategory->update($validated);

        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Kategori berhasil diperbarui.');

            return response()->json(['message' => 'Kategori berhasil diperbarui.']);
        }

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
