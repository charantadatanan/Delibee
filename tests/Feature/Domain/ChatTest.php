<?php

use App\Models\Business;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

it('stamps the first reply when the seller answers', function () {
    $customer = User::factory()->create();
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $conversation = Conversation::factory()->for($business)->for($customer)->create();

    Livewire::actingAs($owner)
        ->test('pages::chat.show', ['conversation' => $conversation])
        ->set('newMessage', 'Thanks for reaching out')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect($conversation->fresh()->first_reply_at)->not->toBeNull()
        ->and(Message::where('conversation_id', $conversation->id)->count())->toBe(1);
});

it('does not stamp the first reply when the customer answers', function () {
    $customer = User::factory()->create();
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $conversation = Conversation::factory()->for($business)->for($customer)->create();

    Livewire::actingAs($customer)
        ->test('pages::chat.show', ['conversation' => $conversation])
        ->set('newMessage', 'Is this still available?')
        ->call('sendMessage')
        ->assertHasNoErrors();

    expect($conversation->fresh()->first_reply_at)->toBeNull();
});

it('marks the other party messages as read', function () {
    $customer = User::factory()->create();
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $conversation = Conversation::factory()->for($business)->for($customer)->create();

    $message = Message::factory()->for($conversation)->create([
        'sender_id' => $customer->id,
        'read_at' => null,
    ]);

    Livewire::actingAs($owner)
        ->test('pages::chat.show', ['conversation' => $conversation])
        ->call('markAsRead')
        ->assertHasNoErrors();

    expect($message->fresh()->read_at)->not->toBeNull();
});

it('starts a conversation from the product page', function () {
    $customer = User::factory()->create();
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $product = Product::factory()->for($business)->create();

    $component = Livewire::actingAs($customer)
        ->test('storefront.product-details', ['business' => $business, 'product' => $product])
        ->call('startChat');

    $conversation = Conversation::firstOrFail();
    $component->assertRedirect(route('chat.show', $conversation));

    expect($conversation->business_id)->toBe($business->id)
        ->and($conversation->user_id)->toBe($customer->id);
});

it('reuses an existing conversation when messaging the seller again', function () {
    $customer = User::factory()->create();
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $product = Product::factory()->for($business)->create();

    $existing = Conversation::factory()->for($business)->for($customer)->create();

    Livewire::actingAs($customer)
        ->test('storefront.product-details', ['business' => $business, 'product' => $product])
        ->call('startChat')
        ->assertRedirect(route('chat.show', $existing));

    expect(Conversation::count())->toBe(1);
});

it('redirects a guest to login when messaging a seller', function () {
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $product = Product::factory()->for($business)->create();

    Livewire::test('storefront.product-details', ['business' => $business, 'product' => $product])
        ->call('startChat')
        ->assertRedirect(route('login'));

    expect(Conversation::count())->toBe(0);
});

it('does not let a seller message their own shop', function () {
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $product = Product::factory()->for($business)->create();

    Livewire::actingAs($owner)
        ->test('storefront.product-details', ['business' => $business, 'product' => $product])
        ->call('startChat')
        ->assertDontSee('Message seller');

    expect(Conversation::count())->toBe(0);
});

it('forbids outsiders from viewing a conversation', function () {
    $customer = User::factory()->create();
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $conversation = Conversation::factory()->for($business)->for($customer)->create();

    $this->actingAs($outsider)
        ->get(route('chat.show', $conversation))
        ->assertForbidden();
});

it('loads only the latest message for each conversation in the inbox', function () {
    $customer = User::factory()->create();

    foreach (range(1, 3) as $i) {
        $business = Business::factory()->create();
        $conversation = Conversation::factory()->for($business)->for($customer)->create();
        Message::factory()->for($conversation)->count(3)->create();
    }

    $component = Livewire::actingAs($customer)->test('pages::chat.index');

    $conversations = collect($component->instance()->conversations()->items());

    expect($conversations)->toHaveCount(3)
        ->and($conversations->every(fn (Conversation $conversation): bool => ! $conversation->relationLoaded('messages')))->toBeTrue()
        ->and($conversations->every(fn (Conversation $conversation): bool => $conversation->latestMessage !== null))->toBeTrue();
});

it('rate limits messages sent in quick succession', function () {
    config(['chat.rate_limit_per_minute' => 2]);

    $customer = User::factory()->create();
    $owner = User::factory()->create();
    $business = Business::factory()->for($owner, 'owner')->create();
    $conversation = Conversation::factory()->for($business)->for($customer)->create();

    $component = Livewire::actingAs($customer)->test('pages::chat.show', ['conversation' => $conversation]);

    $component->set('newMessage', 'First')->call('sendMessage')->assertHasNoErrors();
    $component->set('newMessage', 'Second')->call('sendMessage')->assertHasNoErrors();
    $component->set('newMessage', 'Third')->call('sendMessage');

    expect(Message::where('conversation_id', $conversation->id)->count())->toBe(2);
});
