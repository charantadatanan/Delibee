<?php

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

it('renders the themed Livewire pagination with accessible labels', function () {
    $business = Business::factory()->create();
    $category = Category::factory()->create();
    Product::factory()->count(13)->for($business)->for($category)->create();

    $this->get(route('marketplace'))
        ->assertOk()
        ->assertSee('aria-label="Pagination Navigation"', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('border-honey bg-honey text-ink', false)
        ->assertSee('wire:click="gotoPage(2', false);
});

it('renders the themed plain pagination view', function () {
    $paginator = new LengthAwarePaginator(range(1, 30), 30, 10, 2, ['path' => '/products']);

    $html = $paginator->links('pagination::tailwind')->render();

    expect($html)
        ->toContain('aria-label="Pagination Navigation"')
        ->toContain('aria-current="page"')
        ->toContain('Go to page 1')
        ->toContain('Go to page 3')
        ->toContain('border-honey bg-honey text-ink');
});
