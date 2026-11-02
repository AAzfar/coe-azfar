<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Replace the free-text products.category column with a foreign key to categories,
     * and index the columns the product list filters and sorts on.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->after('sku')->constrained()->restrictOnDelete();
        });

        // Turn every category name already in use into a categories row and link its products
        DB::table('products')->distinct()->pluck('category')->each(function (string $name): void {
            $slug = Str::slug($name);
            $categoryId = DB::table('categories')->where('slug', $slug)->value('id')
                ?? DB::table('categories')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('products')->where('category', $name)->update(['category_id' => $categoryId]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('category');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable(false)->change();
            $table->index('category_id');
            $table->index('name');
            $table->index('price');
            $table->index('stock');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('category')->default('Pantry & Dry Goods');
        });

        DB::table('categories')->get(['id', 'name'])->each(function (object $category): void {
            DB::table('products')->where('category_id', $category->id)->update(['category' => $category->name]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['price']);
            $table->dropIndex(['stock']);
            $table->dropIndex(['created_at']);
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
