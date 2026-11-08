<?php

use App\Models\Business;
use App\Models\Conversation;
use App\Models\Message;
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
