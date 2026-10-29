<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_grocery_product_with_inventory_fields(): void
    {
        $product = [
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category' => 'Fresh Produce',
            'price' => 8.90,
            'stock' => 42,
            'unit' => 'kg',
        ];

        $this->postJson('/api/products', $product)
            ->assertCreated()
            ->assertJsonPath('sku', 'FK-FRT-001')
            ->assertJsonPath('stock', 42)
            ->assertJsonMissingPath('stock_quantity');

        $this->assertDatabaseHas('products', $product);
    }

    public function test_returns_422_when_sku_is_already_used(): void
    {
        Product::create([
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category' => 'Fresh Produce',
            'price' => 8.90,
            'stock' => 42,
            'unit' => 'kg',
        ]);

        $this->postJson('/api/products', [
            'name' => 'Another Apple',
            'sku' => 'FK-FRT-001',
            'category' => 'Fresh Produce',
            'price' => 5.00,
            'stock' => 10,
            'unit' => 'kg',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.sku.0', 'The sku has already been taken.');
    }

    public function test_returns_422_when_required_grocery_fields_are_missing(): void
    {
        $this->postJson('/api/products', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'sku', 'category', 'price', 'stock']);
    }

    public function test_stores_and_returns_product_image_url(): void
    {
        $imageUrl = 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6';

        $id = $this->postJson('/api/products', [
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category' => 'Fresh Produce',
            'price' => 8.90,
            'stock' => 42,
            'unit' => 'kg',
            'image_url' => $imageUrl,
        ])->assertCreated()->json('id');

        $this->getJson("/api/products/{$id}")
            ->assertOk()
            ->assertJsonPath('image_url', $imageUrl);
    }

    public function test_returns_422_when_image_url_is_not_a_url(): void
    {
        $this->postJson('/api/products', [
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category' => 'Fresh Produce',
            'price' => 8.90,
            'stock' => 42,
            'image_url' => 'not-a-url',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['image_url']);
    }
}
