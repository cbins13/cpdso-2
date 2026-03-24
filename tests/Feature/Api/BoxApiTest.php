<?php

use App\Models\Box;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can perform box crud and restore', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $storeResponse = $this->postJson('/api/box', [
        'name' => 'Vegetables',
    ]);

    $storeResponse
        ->assertCreated()
        ->assertJsonPath('name', 'Vegetables');

    $boxId = $storeResponse->json('id');

    Item::query()->create([
        'box_id' => $boxId,
        'name' => 'Carrot',
    ]);

    $this->getJson("/api/box/{$boxId}")
        ->assertOk()
        ->assertJsonPath('name', 'Vegetables')
        ->assertJsonCount(1, 'items')
        ->assertJsonPath('items.0.name', 'Carrot');

    $this->patchJson("/api/box/{$boxId}", [
        'name' => 'Greens',
    ])->assertOk()->assertJsonPath('name', 'Greens');

    $this->deleteJson("/api/box/{$boxId}")
        ->assertOk();

    expect(Box::withTrashed()->findOrFail($boxId)->trashed())->toBeTrue();

    $this->patchJson("/api/box/{$boxId}/restore")
        ->assertOk()
        ->assertJsonPath('name', 'Greens');

    expect(Box::findOrFail($boxId)->trashed())->toBeFalse();
});

test('user cannot access another users box', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $box = Box::query()->create([
        'user_id' => $owner->id,
        'name' => 'Private Box',
    ]);

    Sanctum::actingAs($otherUser);

    $this->getJson("/api/box/{$box->id}")->assertNotFound();
});
