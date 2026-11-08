<?php

namespace App\Livewire\Storefront;

use App\Enums\BusinessStatus;
use App\Enums\ProductStatus;
use App\Models\Business;
use App\Models\Product;
use App\Services\CartService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductDetails extends Component
{
    public Business $business;

    public Product $product;

    public int $quantity = 1;

    public function mount(Business $business, Product $product): void
    {
        abort_unless($product->business_id === $business->id, 404);
        abort_unless($product->status === ProductStatus::Active, 404);
        abort_unless($business->status === BusinessStatus::Active, 404);

        $this->business = $business;
        $this->product = $product->load(['images', 'category', 'business']);
    }

    #[Computed]
    public function inCart(): int
    {
        $user = Auth::user();

        if ($user === null) {
            return 0;
        }

        return app(CartService::class)->quantityOf($user, $this->product);
    }

    public function addToCart(): void
    {
        $user = Auth::user();

        if ($user === null) {
            Flux::toast('Please sign in to start shopping.', variant: 'info');

            $this->redirect(route('login'), navigate: true);

            return;
        }

        if (! $this->product->isInStock()) {
            Flux::toast('That product is out of stock.', variant: 'danger');

            return;
        }

        $remaining = $this->product->stock - $this->inCart();

        if ($this->quantity > $remaining) {
            Flux::toast('There is not enough stock left for that quantity.', variant: 'danger');

            return;
        }

        app(CartService::class)->add($user, $this->product, $this->quantity);

        Flux::toast('Added to your Honey Pot.', variant: 'success');

        unset($this->inCart);
    }

    public function render(): View
    {
        $reviews = $this->product->reviews()
            ->with('user')
            ->published()
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.storefront.product-details', [
            'reviews' => $reviews,
        ]);
    }
}
