<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches licensed Wikimedia Commons stand-in photography to the demo
 * catalogue so a fresh environment does not fall back to letter tiles.
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
        ];

        foreach ($photos as $name => [$file, $alt]) {
            $source = database_path('seeders/assets/products/'.$file);

            if (! is_file($source)) {
                continue;
            }

            $contents = file_get_contents($source);

            if ($contents === false) {
                continue;
            }

            Storage::disk('public')->put('products/'.$file, $contents);

            Product::query()
                ->where('name', $name)
                ->each(function (Product $product) use ($file, $alt): void {
                    ProductImage::updateOrCreate(
                        ['product_id' => $product->id, 'position' => 0],
                        ['path' => 'products/'.$file, 'alt_text' => $alt],
                    );
                });
        }
    }
}
