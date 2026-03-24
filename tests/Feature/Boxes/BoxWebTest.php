<?php

use App\Models\Box;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can open boxes index and details pages', function () {
    $user = User::factory()->create();
    $box = Box::query()->create([
        'user_id' => $user->id,
        'name' => 'Vegetables',
    ]);

    $this->actingAs($user)->get('/boxes')->assertOk();
    $this->actingAs($user)->get("/boxes/{$box->id}")->assertOk();
});

test('authenticated user can create box and item from web routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/boxes', ['name' => 'Vegetables'])
        ->assertRedirect();

    $box = Box::query()->where('user_id', $user->id)->first();

    expect($box)->not()->toBeNull();

    $this->actingAs($user)
        ->post("/boxes/{$box->id}/items", ['name' => 'Carrot'])
        ->assertRedirect();

    expect(Item::query()->where('box_id', $box->id)->count())->toBe(1);
});
