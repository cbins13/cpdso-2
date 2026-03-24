<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api tokens settings page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('api-tokens.index'))
        ->assertOk();
});

test('user can create and revoke a personal access token from settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('api-tokens.store'), [
            'name' => 'Integration Token',
        ])
        ->assertRedirect();

    $token = $user->tokens()->first();

    expect($token)->not()->toBeNull();
    expect($token?->name)->toBe('Integration Token');

    $this->actingAs($user)
        ->delete(route('api-tokens.destroy', ['tokenId' => $token->id]))
        ->assertRedirect();

    expect($user->fresh()->tokens()->count())->toBe(0);
});
