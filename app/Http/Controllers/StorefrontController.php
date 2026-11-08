<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

/**
 * The public-facing side of the marketplace. Only active businesses and
 * active products are visible here; a pending seller is invisible to
 * shoppers until an admin approves it.
 */
class StorefrontController extends Controller
{
    public function marketplace(): View
    {
        return view('storefront.marketplace');
    }

    public function categories(): View
    {
        return view('storefront.categories');
    }

    public function category(Category $category): View
    {
        return view('storefront.category', [
            'category' => $category,
        ]);
    }

    public function business(Business $business): View
    {
        abort_unless($business->isRankable(), 404);

        return view('storefront.business', [
            'business' => $business,
        ]);
    }

    public function product(Business $business, string $product): View
    {
        abort_unless($business->isRankable(), 404);

        $product = Product::query()
            ->active()
            ->where('business_id', $business->id)
            ->where('slug', $product)
            ->firstOrFail();

        return view('storefront.product', [
            'business' => $business,
            'product' => $product,
        ]);
    }

    public function ranking(): View
    {
        return view('storefront.ranking');
    }
}
