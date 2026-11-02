<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_lists_categories_alphabetically_with_product_counts(): void
    {
        $bakery = Category::factory()->create(['name' => 'Bakery']);
        Category::factory()->create(['name' => 'Beverages']);
        Product::factory()->count(2)->for($bakery)->create();

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.*.name', ['Bakery', 'Beverages'])
            ->assertJsonPath('data.0.products_count', 2)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_searches_and_paginates_categories(): void
    {
        Category::factory()->create(['name' => 'Frozen Foods']);
        Category::factory()->create(['name' => 'Fresh Foods']);
        Category::factory()->create(['name' => 'Snacks']);

        $this->getJson('/api/categories?search=foods&per_page=1&sort=-name')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Frozen Foods')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_shows_a_category(): void
    {
        $category = Category::factory()->create();

        $this->getJson("/api/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.slug', $category->slug);
    }
}
