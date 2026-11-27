<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches licensed Wikimedia Commons stand-in photography to the demo
 * catalogue so a fresh environment does not fall back to letter tiles.
 *
 * The existing six photographs are reused across the new listings; no new
 * assets are downloaded. Every demo product name is mapped here.
 */
class DemoPhotoSeeder extends Seeder
{
    public function run(): void
    {
        $photos = [
            'Fresh Apples' => ['fresh-apples.jpg', 'Red apples on a wooden table'],
            'Golden Mangoes' => ['golden-mangoes.jpg', 'Whole and halved ripe mangoes'],
            'Free Range Eggs' => ['free-range-eggs.jpg', 'Free-range brown eggs in a carton'],
            'Wild Honey' => ['wild-honey.jpg', 'A glass jar of honey'],
            'Sourdough Loaf' => ['sourdough-loaf.jpg', 'A round sourdough loaf on a cooling rack'],
            'Cold Brew Coffee' => ['cold-brew-coffee.jpg', 'A tall glass of iced coffee'],
            'Honeycrisp Apples' => ['fresh-apples.jpg', 'Red apples on a wooden table'],
            'Orchard Honey' => ['wild-honey.jpg', 'A glass jar of honey'],
            'Cold Brew Bottle' => ['cold-brew-coffee.jpg', 'A tall glass of iced coffee'],
            'Pasture Duck Eggs' => ['free-range-eggs.jpg', 'Free-range brown eggs in a carton'],
            'Seeded Sourdough' => ['sourdough-loaf.jpg', 'A round sourdough loaf on a cooling rack'],
            'Wildflower Honey' => ['wild-honey.jpg', 'A glass jar of honey'],
            'Iced Coffee' => ['cold-brew-coffee.jpg', 'A tall glass of iced coffee'],
            'Honey Toast' => ['sourdough-loaf.jpg', 'A round sourdough loaf on a cooling rack'],
            'Egg Sandwich' => ['free-range-eggs.jpg', 'Free-range brown eggs in a carton'],
            'Carabao Mangoes' => ['golden-mangoes.jpg', 'Whole and halved ripe mangoes'],
            'Sourdough Starter' => ['sourdough-loaf.jpg', 'A round sourdough loaf on a cooling rack'],
            'Wild Honey Sticks' => ['wild-honey.jpg', 'A glass jar of honey'],
        ];

        foreach ($photos as $name => [$file, $alt]) {
            $source = database_path('seeders/assets/products/'.$file);

            if (! is_file($source)) {
                continue;
            }

            $path = 'products/'.$file;

            if (! Storage::disk('public')->exists($path)) {
                $contents = file_get_contents($source);

                if ($contents === false) {
                    continue;
                }

                Storage::disk('public')->put($path, $contents);
            }

            Product::query()
                ->where('name', $name)
                ->each(function (Product $product) use ($path, $alt): void {
                    // Existing images (including ones supplied by a seller)
                    // are never rewritten by the demo seeder.
                    ProductImage::firstOrCreate(
                        ['product_id' => $product->id, 'position' => 0],
                        ['path' => $path, 'alt_text' => $alt],
                    );
                });
        }
    }
}
