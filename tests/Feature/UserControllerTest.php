<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can get list of users', function () {
    User::factory()->count(5)->create();

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(6); // 5 + 1 admin
});

test('users list is ordered by name', function () {
    User::factory()->create(['name' => 'Zack']);
    User::factory()->create(['name' => 'Alice']);
    User::factory()->create(['name' => 'Bob']);

    $admin = User::factory()->create(['name' => 'Admin']);

    $response = $this->actingAs($admin)
        ->getJson('/api/users')
        ->assertOk();

    $names = collect($response->json())->pluck('name')->toArray();
    
    // Verify it's sorted
    $sorted = $names;
    sort($sorted);
    
    expect($names)->toBe($sorted);
});

test('guest cannot access users list', function () {
    $this->getJson('/api/users')
        ->assertUnauthorized();
});

test('users endpoint returns only id and name', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com'
    ]);

    $admin = User::factory()->create();

    $response = $this->actingAs($admin)
        ->getJson('/api/users')
        ->assertOk();

    $userData = collect($response->json())->firstWhere('id', $user->id);
    
    expect($userData)->toHaveKeys(['id', 'name']);
});
