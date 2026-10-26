<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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

    public function catalog(): View
    {
        $products = Product::query()
            ->with('category')
            ->orderBy('id')
            ->paginate(6)
            ->withQueryString();

        return view('product.description', compact('products'));
    }

    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->orderBy('id')
            ->paginate(10);

        return view('dashboard.products.index', compact('products'));
    }

    public function create(): View
    {
        $product = new Product;
        $categories = ProductCategory::query()->orderBy('name')->get();

        return view('dashboard.products.form', compact('product', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $image = $validated['image'];
        unset($validated['image']);

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['image'] = $this->storeImage($image);
        Product::query()->create($validated);

        return to_route('dashboard.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $categories = ProductCategory::query()->orderBy('name')->get();

        return view('dashboard.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $image = $validated['image'] ?? null;
        unset($validated['image']);
        $validated['slug'] = $this->uniqueSlug($validated['name'], $product->id);

        if ($image instanceof UploadedFile) {
            $oldImage = $product->image;
            $validated['image'] = $this->storeImage($image);
            $product->update($validated);
            $this->deleteManagedImage($oldImage);
        } else {
            $product->update($validated);
        }

        return to_route('dashboard.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if (Order::query()->where('product_id', $product->id)->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah tercatat dalam pesanan.');
        }

        $image = $product->image;
        $product->delete();
        $this->deleteManagedImage($image);

        return to_route('dashboard.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $suffix = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }

    private function storeImage(UploadedFile $image): string
    {
        $directory = public_path('images/products');
        File::ensureDirectoryExists($directory);
        $filename = 'admin-'.Str::uuid().'.'.$image->extension();
        $image->move($directory, $filename);

        return 'images/products/'.$filename;
    }

    private function deleteManagedImage(?string $image): void
    {
        if ($image !== null && preg_match('/\Aimages\/products\/admin-[a-f0-9-]+\.(jpg|jpeg|png|webp)\z/i', $image)) {
            File::delete(public_path($image));
        }
    }
}
