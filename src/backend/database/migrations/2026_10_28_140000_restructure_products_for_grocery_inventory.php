<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasLegacySchema = Schema::hasColumn('products', 'description') || Schema::hasColumn('products', 'stock_quantity');

        if (! $hasLegacySchema) {
            return;
        }

        $hasLegacyStock = Schema::hasColumn('products', 'stock_quantity');
        $hasStock = Schema::hasColumn('products', 'stock');

        Schema::table('products', function (Blueprint $table) use ($hasStock): void {
            if (! Schema::hasColumn('products', 'sku')) {
                $table->string('sku')->nullable();
            }

            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category')->default('Pantry & Dry Goods');
            }

            if (! $hasStock) {
                $table->unsignedInteger('stock')->default(0);
            }

            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->nullable();
            }
        });

        if ($hasLegacyStock && ! $hasStock) {
            DB::table('products')->update([
                'stock' => DB::raw('stock_quantity'),
            ]);
        }

        DB::table('products')->orderBy('id')->get(['id', 'sku', 'unit'])->each(function (object $product): void {
            $updates = [];

            if (! $product->sku) {
                $updates['sku'] = 'FK-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT);
            }

            if (! $product->unit) {
                $updates['unit'] = 'piece';
            }

            if ($updates) {
                DB::table('products')->where('id', $product->id)->update($updates);
            }
        });

        $indexes = Schema::getIndexes('products');
        $hasSkuIndex = collect($indexes)->contains(fn (array $index): bool => $index['name'] === 'products_sku_unique');

        if (! $hasSkuIndex) {
            Schema::table('products', function (Blueprint $table): void {
                $table->unique('sku');
            });
        }
    }

    public function down(): void
    {
        $hasLegacySchema = Schema::hasColumn('products', 'description') || Schema::hasColumn('products', 'stock_quantity');

        if (! $hasLegacySchema) {
            return;
        }

        $indexes = Schema::getIndexes('products');
        $hasSkuIndex = collect($indexes)->contains(fn (array $index): bool => $index['name'] === 'products_sku_unique');

        Schema::table('products', function (Blueprint $table) use ($hasSkuIndex): void {
            if ($hasSkuIndex) {
                $table->dropUnique('products_sku_unique');
            }

            $columns = array_values(array_filter(
                ['sku', 'category', 'stock', 'unit'],
                fn (string $column): bool => Schema::hasColumn('products', $column),
            ));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
