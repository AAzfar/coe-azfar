<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GrocerySchemaMigrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_upgrades_legacy_products_and_preserves_old_data_through_rollback(): void
    {
        Schema::drop('products');
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity');
            $table->timestamps();
        });

        DB::table('products')->insert([
            'name' => 'Green apples',
            'description' => 'Locally grown',
            'price' => 10.99,
            'stock_quantity' => 12,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_10_28_140000_restructure_products_for_grocery_inventory.php');
        $migration->up();

        $product = DB::table('products')->first();
        $this->assertSame('FK-000001', $product->sku);
        $this->assertSame('Pantry & Dry Goods', $product->category);
        $this->assertSame(12, $product->stock);
        $this->assertSame('piece', $product->unit);
        $this->assertSame('Locally grown', $product->description);

        $migration->down();

        $this->assertTrue(Schema::hasColumn('products', 'description'));
        $this->assertTrue(Schema::hasColumn('products', 'stock_quantity'));
        $this->assertFalse(Schema::hasColumn('products', 'sku'));
    }
}
