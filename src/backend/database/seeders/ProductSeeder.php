<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Fuji Apples', 'sku' => 'FK-FRT-001', 'category' => 'Fresh Foods', 'price' => 8.90, 'stock' => 42, 'unit' => 'kg', 'image_url' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Cavendish Bananas', 'sku' => 'FK-FRT-002', 'category' => 'Fresh Foods', 'price' => 5.50, 'stock' => 60, 'unit' => 'kg', 'image_url' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Navel Oranges', 'sku' => 'FK-FRT-003', 'category' => 'Fresh Foods', 'price' => 9.80, 'stock' => 35, 'unit' => 'kg', 'image_url' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Cherry Tomatoes', 'sku' => 'FK-VEG-001', 'category' => 'Fresh Foods', 'price' => 7.90, 'stock' => 28, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Baby Spinach', 'sku' => 'FK-VEG-002', 'category' => 'Fresh Foods', 'price' => 6.50, 'stock' => 24, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Russet Potatoes', 'sku' => 'FK-VEG-003', 'category' => 'Fresh Foods', 'price' => 4.20, 'stock' => 45, 'unit' => 'kg', 'image_url' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Free-Range Eggs', 'sku' => 'FK-DAI-001', 'category' => 'Dairy & Eggs', 'price' => 13.90, 'stock' => 32, 'unit' => 'box', 'image_url' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Fresh Whole Milk', 'sku' => 'FK-DAI-002', 'category' => 'Dairy & Eggs', 'price' => 8.20, 'stock' => 26, 'unit' => 'bottle', 'image_url' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Greek Yogurt', 'sku' => 'FK-DAI-003', 'category' => 'Dairy & Eggs', 'price' => 10.50, 'stock' => 20, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Sourdough Loaf', 'sku' => 'FK-BAK-001', 'category' => 'Bakery', 'price' => 12.00, 'stock' => 18, 'unit' => 'piece', 'image_url' => 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Butter Croissants', 'sku' => 'FK-BAK-002', 'category' => 'Bakery', 'price' => 9.50, 'stock' => 22, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Chicken Breast', 'sku' => 'FK-MEA-001', 'category' => 'Meat & Seafood', 'price' => 18.90, 'stock' => 25, 'unit' => 'kg', 'image_url' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Atlantic Salmon Fillet', 'sku' => 'FK-MEA-002', 'category' => 'Meat & Seafood', 'price' => 42.00, 'stock' => 12, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Jasmine Rice', 'sku' => 'FK-PAN-001', 'category' => 'Pantry & Dry Goods', 'price' => 16.90, 'stock' => 40, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Extra Virgin Olive Oil', 'sku' => 'FK-PAN-002', 'category' => 'Pantry & Dry Goods', 'price' => 32.50, 'stock' => 16, 'unit' => 'bottle', 'image_url' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Rolled Oats', 'sku' => 'FK-PAN-003', 'category' => 'Pantry & Dry Goods', 'price' => 11.90, 'stock' => 30, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1614961233913-a5113a4a34ed?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Ground Coffee', 'sku' => 'FK-BEV-001', 'category' => 'Beverages', 'price' => 24.00, 'stock' => 14, 'unit' => 'pack', 'image_url' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=900&q=85'],
            ['name' => 'Sparkling Water', 'sku' => 'FK-BEV-002', 'category' => 'Beverages', 'price' => 4.50, 'stock' => 50, 'unit' => 'bottle', 'image_url' => 'https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=900&q=85'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'unit' => $product['unit'],
                    'image_url' => $product['image_url'],
                ],
            );
        }
    }
}
