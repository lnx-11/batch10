<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File as FileRule;

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

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'sort' => ['nullable', Rule::in(['name_asc', 'name_desc', 'price_asc', 'price_desc'])],
        ]);

        $search = trim($filters['search'] ?? '');
        $sort = $filters['sort'] ?? 'name_asc';
        $categories = ProductCategory::query()->orderBy('name')->get();

        $query = Product::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('product_category_id', $categoryId));

        match ($sort) {
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy('price')->orderBy('name'),
            'price_desc' => $query->orderByDesc('price')->orderBy('name'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(10)->withQueryString();

        return view('dashboard.products.index', compact('products', 'categories', 'search', 'sort'));
    }

    public function create(): View|JsonResponse
    {
        $product = new Product;
        $categories = ProductCategory::query()->orderBy('name')->get();

        if (request()->header('X-Admin-Modal') === 'true') {
            return response()->json([
                'html' => view('dashboard.products.modal-form', compact('product', 'categories'))->render(),
            ]);
        }

        return view('dashboard.products.form', compact('product', 'categories'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'image' => ['required', FileRule::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2048), 'dimensions:min_width=100,min_height=100,max_width=2048,max_height=2048'],
        ]);

        $image = $validated['image'];
        unset($validated['image']);

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $validated['image'] = $this->storeImage($image);
        $product = Product::query()->create($validated);

        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Produk berhasil ditambahkan.');

            return response()->json([
                'message' => 'Produk berhasil ditambahkan.',
                'product' => $product,
            ]);
        }

        return to_route('dashboard.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View|JsonResponse
    {
        $categories = ProductCategory::query()->orderBy('name')->get();

        if (request()->header('X-Admin-Modal') === 'true') {
            return response()->json([
                'html' => view('dashboard.products.modal-form', compact('product', 'categories'))->render(),
            ]);
        }

        return view('dashboard.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', FileRule::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(2048), 'dimensions:min_width=100,min_height=100,max_width=2048,max_height=2048'],
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

        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Produk berhasil diperbarui.');

            return response()->json(['message' => 'Produk berhasil diperbarui.']);
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
