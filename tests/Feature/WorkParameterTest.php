<?php

use App\Models\User;
use App\Models\WorkParameter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create admin user for tests
    $this->admin = User::factory()->create();
    // Assuming you have Spatie permissions
    // $this->admin->assignRole('admin');
});

test('admin can list work parameters', function () {
    WorkParameter::factory()->count(3)->create();

    $this->actingAs($this->admin)
        ->getJson('/api/work-parameters')
        ->assertOk()
        ->assertJsonCount(3);
});

test('admin can create work parameter', function () {
    $data = [
        'name' => 'Test Parameter',
        'fields' => ['field1', 'field2']
    ];

    $this->actingAs($this->admin)
        ->postJson('/api/work-parameters', $data)
        ->assertCreated()
        ->assertJson([
            'message' => 'Parametro creato con successo'
        ]);

    $this->assertDatabaseHas('work_parameters', [
        'name' => 'Test Parameter'
    ]);
});

test('cannot create work parameter with duplicate name', function () {
    WorkParameter::factory()->create(['name' => 'Existing Parameter']);

    $data = [
        'name' => 'Existing Parameter',
        'fields' => []
    ];

    $this->actingAs($this->admin)
        ->postJson('/api/work-parameters', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('admin can update work parameter', function () {
    $parameter = WorkParameter::factory()->create(['name' => 'Old Name']);

    $data = [
        'name' => 'New Name',
        'fields' => ['updated_field']
    ];

    $this->actingAs($this->admin)
        ->putJson("/api/work-parameters/{$parameter->id}", $data)
        ->assertOk()
        ->assertJson([
            'message' => 'Parametro aggiornato con successo'
        ]);

    $this->assertDatabaseHas('work_parameters', [
        'id' => $parameter->id,
        'name' => 'New Name'
    ]);
});

test('admin can delete work parameter', function () {
    $parameter = WorkParameter::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson("/api/work-parameters/{$parameter->id}")
        ->assertOk()
        ->assertJson([
            'message' => 'Parametro eliminato con successo'
        ]);

    $this->assertDatabaseMissing('work_parameters', [
        'id' => $parameter->id
    ]);
});

test('guest cannot access work parameters', function () {
    $this->getJson('/api/work-parameters')
        ->assertUnauthorized();
});

test('work parameter name is required', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/work-parameters', ['fields' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('work parameter name cannot exceed 255 characters', function () {
    $data = [
        'name' => str_repeat('a', 256),
        'fields' => []
    ];

    $this->actingAs($this->admin)
        ->postJson('/api/work-parameters', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('work parameter fields must be array', function () {
    $data = [
        'name' => 'Test Parameter',
        'fields' => 'not-an-array'
    ];

    $this->actingAs($this->admin)
        ->postJson('/api/work-parameters', $data)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['fields']);
});
