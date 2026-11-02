<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    private const SORTABLE_FIELDS = ['name', 'slug', 'created_at'];

    /**
     * GET /api/categories
     *
     * Query: search, is_active, sort, page, per_page.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            ...$this->listRules(self::SORTABLE_FIELDS),
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $categories = Category::query()
            ->withCount('products')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereRaw("lower(name) like ? escape '\\'", [$this->likePattern($search)]))
            ->when(isset($filters['is_active']), fn (Builder $query) => $query->where('is_active', $request->boolean('is_active')));

        return CategoryResource::collection(
            $this->applySort($categories, $filters['sort'] ?? null, 'name')
                ->paginate($filters['per_page'] ?? self::DEFAULT_PER_PAGE)
                ->withQueryString(),
        );
    }

    /**
     * GET /api/categories/{category}
     */
    public function show(Category $category): CategoryResource
    {
        return CategoryResource::make($category->loadCount('products'));
    }
}
