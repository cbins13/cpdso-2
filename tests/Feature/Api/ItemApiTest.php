<?php

use App\Models\Box;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can perform item crud and restore', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $box = Box::query()->create([
        'user_id' => $user->id,
        'name' => 'Vegetables',
    ]);

    $storeResponse = $this->postJson('/api/item', [
        'box_id' => $box->id,
        'name' => 'Carrot',
    ]);

    $storeResponse
        ->assertCreated()
        ->assertJsonPath('name', 'Carrot');

    $itemId = $storeResponse->json('id');

    $this->getJson("/api/item/{$itemId}")
        ->assertOk()
        ->assertJsonPath('name', 'Carrot');

    $this->patchJson("/api/item/{$itemId}", [
        'name' => 'Spinach',
    ])->assertOk()->assertJsonPath('name', 'Spinach');

    $this->deleteJson("/api/item/{$itemId}")
        ->assertOk();

    expect(Item::withTrashed()->findOrFail($itemId)->trashed())->toBeTrue();

    $this->patchJson("/api/item/{$itemId}/restore")
        ->assertOk()
        ->assertJsonPath('name', 'Spinach');

    expect(Item::findOrFail($itemId)->trashed())->toBeFalse();
});

test('user cannot create item in another users box', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $box = Box::query()->create([
        'user_id' => $owner->id,
        'name' => 'Owner Box',
    ]);

    Sanctum::actingAs($otherUser);

    $this->postJson('/api/item', [
        'box_id' => $box->id,
        'name' => 'Unauthorized Item',
    ])->assertUnprocessable();
});
