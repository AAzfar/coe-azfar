<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
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
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
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
