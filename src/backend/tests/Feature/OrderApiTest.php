<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_places_an_order_and_takes_items_out_of_stock(): void
    {
        $apples = Product::factory()->create(['price' => 8.90, 'stock' => 10]);
        $milk = Product::factory()->create(['price' => 8.20, 'stock' => 3]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Aina',
            'customer_email' => 'aina@example.com',
            'items' => [
                ['product_id' => $apples->id, 'quantity' => 2],
                ['product_id' => $milk->id, 'quantity' => 3],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', '42.40')
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.items.0.unit_price', '8.90');

        $this->assertSame(8, $apples->fresh()->stock);
        $this->assertSame(0, $milk->fresh()->stock);
    }

    public function test_rejects_an_order_for_more_than_is_in_stock_and_leaves_stock_untouched(): void
    {
        $milk = Product::factory()->create(['name' => 'Milk', 'stock' => 1]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Aina',
            'customer_email' => 'aina@example.com',
            'items' => [['product_id' => $milk->id, 'quantity' => 2]],
        ])->assertUnprocessable()
            ->assertJsonPath('error.details', ['items.0.quantity' => ['Only 1 of Milk left in stock.']]);

        $this->assertSame(1, $milk->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_filters_and_sorts_orders(): void
    {
        Order::create(['customer_name' => 'Aina', 'customer_email' => 'aina@example.com', 'status' => 'paid', 'total' => 20]);
        Order::create(['customer_name' => 'Ben', 'customer_email' => 'ben@example.com', 'status' => 'paid', 'total' => 50]);
        Order::create(['customer_name' => 'Chen', 'customer_email' => 'chen@example.com', 'status' => 'pending', 'total' => 10]);

        $this->getJson('/api/orders?status=paid&sort=-total')
            ->assertOk()
            ->assertJsonPath('data.*.customer_name', ['Ben', 'Aina'])
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/orders?search=CHEN')
            ->assertJsonPath('data.*.customer_name', ['Chen']);

        $this->getJson('/api/orders?status=lost')
            ->assertUnprocessable()
            ->assertJsonStructure(['error' => ['details' => ['status']]]);
    }
}
