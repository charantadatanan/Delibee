<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The Flower Fields: the marketplace categories.
 */
class CategorySeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, icon: string, description: string}>
     */
    private const CATEGORIES = [
        ['name' => 'Fresh Produce', 'icon' => 'carrot', 'description' => 'Fruits and vegetables picked this week.'],
        ['name' => 'Dairy & Eggs', 'icon' => 'beaker', 'description' => 'Milk, cheese, butter and farm eggs.'],
        ['name' => 'Meat & Seafood', 'icon' => 'fish', 'description' => 'Butcher cuts, seafood and charcuterie.'],
        ['name' => 'Bakery', 'icon' => 'cake', 'description' => 'Bread, pastries and baked goods.'],
        ['name' => 'Pantry', 'icon' => 'archive-box', 'description' => 'Rice, oil, spices and shelf-stable goods.'],
        ['name' => 'Drinks', 'icon' => 'cup', 'description' => 'Coffee, tea, juices and local brews.'],
        ['name' => 'Homegrown', 'icon' => 'sun', 'description' => 'Handmade and small-batch goods from local makers.'],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $position => $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'icon' => $category['icon'],
                    'position' => $position + 1,
                    'is_active' => true,
                ],
            );
        }
    }
}
