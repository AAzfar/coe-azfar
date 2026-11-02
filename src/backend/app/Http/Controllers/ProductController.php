<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private const SORTABLE_FIELDS = ['name', 'sku', 'price', 'stock', 'created_at'];

    /**
     * GET /api/products
     *
     * Query: search, category (slug), category_id, min_price, max_price, in_stock, sort, page, per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            ...$this->listRules(self::SORTABLE_FIELDS),
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
            'in_stock' => ['sometimes', 'boolean'],
        ]);

        $products = Product::query()
            ->with('category')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $pattern = $this->likePattern($search);
                $query->where(function (Builder $query) use ($pattern): void {
                    $query->whereRaw("lower(name) like ? escape '\\'", [$pattern])
                        ->orWhereRaw("lower(sku) like ? escape '\\'", [$pattern])
                        ->orWhereHas('category', fn (Builder $category) => $category->whereRaw("lower(name) like ? escape '\\'", [$pattern]));
                });
            })
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query->whereRelation('category', 'slug', $slug))
            ->when($filters['category_id'] ?? null, fn (Builder $query, int|string $categoryId) => $query->where('category_id', $categoryId))
            ->when(isset($filters['min_price']), fn (Builder $query) => $query->where('price', '>=', $filters['min_price']))
            ->when(isset($filters['max_price']), fn (Builder $query) => $query->where('price', '<=', $filters['max_price']))
            ->when(isset($filters['in_stock']), fn (Builder $query) => $query->where('stock', $request->boolean('in_stock') ? '>' : '=', 0));

        return ProductResource::collection(
            $this->applySort($products, $filters['sort'] ?? null, 'name')
                ->paginate($filters['per_page'] ?? self::DEFAULT_PER_PAGE)
                ->withQueryString(),
        );
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        $product = Product::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'stock' => ['required', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]));

        return ProductResource::make($product->load('category'))->response()->setStatusCode(201);
    }

    /**
     * GET /api/products/{product}
     */
    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product->load('category'));
    }

    /**
     * PUT/PATCH /api/products/{product}
     */
    public function update(Request $request, Product $product): ProductResource
    {
        $product->update($request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999.99'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
        ]));

        return ProductResource::make($product->load('category'));
    }

    /**
     * DELETE /api/products/{product}
     */
    public function destroy(Product $product): JsonResponse
    {
        abort_if(
            $product->orderItems()->exists(),
            409,
            'This product appears on existing orders and cannot be deleted. Set its stock to 0 instead.',
        );

        $product->delete();

        return response()->json(null, 204);
    }
}
