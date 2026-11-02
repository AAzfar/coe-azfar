<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_grocery_product_with_inventory_fields(): void
    {
        $category = Category::factory()->create(['name' => 'Fresh Foods', 'slug' => 'fresh-foods']);
        $product = [
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category_id' => $category->id,
            'price' => 8.90,
            'stock' => 42,
            'unit' => 'kg',
        ];

        $this->postJson('/api/products', $product)
            ->assertCreated()
            ->assertJsonPath('data.sku', 'FK-FRT-001')
            ->assertJsonPath('data.stock', 42)
            ->assertJsonPath('data.category.slug', 'fresh-foods');

        $this->assertDatabaseHas('products', $product);
    }

    public function test_shows_updates_and_deletes_a_product(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.category.id', $product->category_id);

        $this->patchJson("/api/products/{$product->id}", ['stock' => 9])
            ->assertOk()
            ->assertJsonPath('data.stock', 9);

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();
        $this->assertModelMissing($product);
    }

    public function test_refuses_to_delete_a_product_that_has_been_ordered(): void
    {
        $product = Product::factory()->create();
        Order::create(['customer_name' => 'Aina', 'customer_email' => 'aina@example.com'])
            ->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

        $this->deleteJson("/api/products/{$product->id}")
            ->assertConflict()
            ->assertJsonPath('error.code', 'conflict');

        $this->assertModelExists($product);
    }

    public function test_returns_422_when_sku_is_already_used(): void
    {
        $existing = Product::factory()->create(['sku' => 'FK-FRT-001']);

        $this->postJson('/api/products', [
            'name' => 'Another Apple',
            'sku' => 'FK-FRT-001',
            'category_id' => $existing->category_id,
            'price' => 5.00,
            'stock' => 10,
            'unit' => 'kg',
        ])->assertUnprocessable()
            ->assertJsonPath('error.details.sku.0', 'The sku has already been taken.');
    }

    public function test_returns_422_when_required_grocery_fields_are_missing(): void
    {
        $this->postJson('/api/products', [])
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_failed')
            ->assertJsonStructure(['error' => ['details' => ['name', 'sku', 'category_id', 'price', 'stock']]]);
    }

    public function test_returns_422_when_category_does_not_exist(): void
    {
        $this->postJson('/api/products', [
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category_id' => 999,
            'price' => 8.90,
            'stock' => 42,
        ])->assertUnprocessable()
            ->assertJsonStructure(['error' => ['details' => ['category_id']]]);
    }

    public function test_stores_and_returns_product_image_url(): void
    {
        $imageUrl = 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6';

        $id = $this->postJson('/api/products', [
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category_id' => Category::factory()->create()->id,
            'price' => 8.90,
            'stock' => 42,
            'unit' => 'kg',
            'image_url' => $imageUrl,
        ])->assertCreated()->json('data.id');

        $this->getJson("/api/products/{$id}")
            ->assertOk()
            ->assertJsonPath('data.image_url', $imageUrl);
    }

    public function test_returns_422_when_image_url_is_not_a_url(): void
    {
        $this->postJson('/api/products', [
            'name' => 'Fuji Apples',
            'sku' => 'FK-FRT-001',
            'category_id' => Category::factory()->create()->id,
            'price' => 8.90,
            'stock' => 42,
            'image_url' => 'not-a-url',
        ])->assertUnprocessable()
            ->assertJsonStructure(['error' => ['details' => ['image_url']]]);
    }

    public function test_paginates_the_product_list(): void
    {
        Product::factory()->count(5)->create();

        $this->getJson('/api/products?per_page=2&page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_searches_products_by_name_or_sku_ignoring_case(): void
    {
        Product::factory()->create(['name' => 'Fuji Apples', 'sku' => 'FK-FRT-001']);
        Product::factory()->create(['name' => 'Greek Yogurt', 'sku' => 'FK-DAI-003']);

        $this->getJson('/api/products?search=APPLE')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Fuji Apples');

        $this->getJson('/api/products?search=dai-003')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Greek Yogurt');
    }

    public function test_filters_products_by_category_price_and_stock(): void
    {
        $bakery = Category::factory()->create(['slug' => 'bakery']);
        Product::factory()->for($bakery)->create(['name' => 'Sourdough', 'price' => 12, 'stock' => 4]);
        Product::factory()->for($bakery)->create(['name' => 'Baguette', 'price' => 6, 'stock' => 0]);
        Product::factory()->create(['name' => 'Salmon', 'price' => 42, 'stock' => 3]);

        $this->getJson('/api/products?category=bakery')
            ->assertJsonPath('meta.total', 2);

        $this->getJson("/api/products?category_id={$bakery->id}&in_stock=1")
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Sourdough');

        $this->getJson('/api/products?min_price=10&max_price=20')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Sourdough');

        $this->getJson('/api/products?in_stock=0')
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Baguette');
    }

    public function test_sorts_products_ascending_and_descending(): void
    {
        Product::factory()->create(['name' => 'Bananas', 'price' => 5.50]);
        Product::factory()->create(['name' => 'Apples', 'price' => 8.90]);
        Product::factory()->create(['name' => 'Coffee', 'price' => 24.00]);

        $this->getJson('/api/products')
            ->assertJsonPath('data.*.name', ['Apples', 'Bananas', 'Coffee']);

        $this->getJson('/api/products?sort=-price')
            ->assertJsonPath('data.*.name', ['Coffee', 'Apples', 'Bananas']);
    }

    public function test_rejects_unknown_sort_fields_and_oversized_pages(): void
    {
        $this->getJson('/api/products?sort=password&per_page=500')
            ->assertUnprocessable()
            ->assertJsonStructure(['error' => ['details' => ['sort', 'per_page']]]);
    }
}
