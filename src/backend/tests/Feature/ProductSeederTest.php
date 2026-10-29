<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeding_twice_keeps_the_grocery_catalog_at_eighteen_unique_skus(): void
    {
        $this->seed(ProductSeeder::class);
        $this->seed(ProductSeeder::class);

        $this->assertSame(18, Product::count());
        $this->assertSame(18, Product::whereNotNull('sku')->distinct('sku')->count('sku'));
    }
}
