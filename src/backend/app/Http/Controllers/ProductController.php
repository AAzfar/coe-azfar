<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * GET /api/products
     */
    public function index(): JsonResponse
    {
        return response()->json(Product::latest()->get());
    }

    /**
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        $product = Product::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'category' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]));

        return response()->json($product, 201);
    }

    /**
     * GET /api/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * PUT/PATCH /api/products/{product}
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $product->update($request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'sku' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'category' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
        ]));

        return response()->json($product);
    }

    /**
     * DELETE /api/products/{product}
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
