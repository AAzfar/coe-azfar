<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Fresh Foods', 'slug' => 'fresh-foods'],
            ['name' => 'Pantry & Dry Goods', 'slug' => 'pantry-dry-goods'],
            ['name' => 'Snacks', 'slug' => 'snacks'],
            ['name' => 'Beverages', 'slug' => 'beverages'],
            ['name' => 'Frozen Foods', 'slug' => 'frozen-foods'],
            ['name' => 'Bakery', 'slug' => 'bakery'],
            ['name' => 'Dairy & Eggs', 'slug' => 'dairy-eggs'],
            ['name' => 'Meat & Seafood', 'slug' => 'meat-seafood'],
            ['name' => 'Health & Wellness', 'slug' => 'health-wellness'],
            ['name' => 'Household Essentials', 'slug' => 'household-essentials'],
            ['name' => 'Baby & Kids', 'slug' => 'baby-kids'],
            ['name' => 'Pet Supplies', 'slug' => 'pet-supplies'],
            ['name' => 'International Foods', 'slug' => 'international-foods'],
            ['name' => 'Organic & Natural', 'slug' => 'organic-natural'],
            ['name' => 'Specialty Diets', 'slug' => 'specialty-diets'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']]
            );
        }
    }
}
