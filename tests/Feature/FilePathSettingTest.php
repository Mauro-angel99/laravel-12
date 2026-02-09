<?php

use App\Models\User;
use App\Models\FilePathSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    // $this->admin->assignRole('admin');
});

test('can get file path settings', function () {
    FilePathSetting::create([
        'pdf_path' => 'C:\\PDFs',
        'image_path' => 'C:\\Images'
    ]);

    $this->actingAs($this->admin)
        ->getJson('/api/file-path-settings')
        ->assertOk()
        ->assertJson([
            'pdf_path' => 'C:\\PDFs',
            'image_path' => 'C:\\Images'
        ]);
});

test('returns empty paths when no settings exist', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/file-path-settings')
        ->assertOk()
        ->assertJson([
            'pdf_path' => '',
            'image_path' => ''
        ]);
});

test('can update file path settings', function () {
    $data = [
        'pdf_path' => 'C:\\NewPDFs',
        'image_path' => 'C:\\NewImages'
    ];

    $this->actingAs($this->admin)
        ->putJson('/api/file-path-settings', $data)
        ->assertOk()
        ->assertJson([
            'message' => 'Percorsi aggiornati con successo'
        ]);

    $this->assertDatabaseHas('file_path_settings', $data);
});

test('creates settings if none exist', function () {
    $this->assertDatabaseCount('file_path_settings', 0);

    $data = [
        'pdf_path' => 'C:\\PDFs',
        'image_path' => 'C:\\Images'
    ];

    $this->actingAs($this->admin)
        ->putJson('/api/file-path-settings', $data)
        ->assertOk();

    $this->assertDatabaseCount('file_path_settings', 1);
});

test('pdf path is required', function () {
    $this->actingAs($this->admin)
        ->putJson('/api/file-path-settings', [
            'image_path' => 'C:\\Images'
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['pdf_path']);
});

test('image path is required', function () {
    $this->actingAs($this->admin)
        ->putJson('/api/file-path-settings', [
            'pdf_path' => 'C:\\PDFs'
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image_path']);
});

test('paths cannot exceed 500 characters', function () {
    $longPath = str_repeat('a', 501);

    $this->actingAs($this->admin)
        ->putJson('/api/file-path-settings', [
            'pdf_path' => $longPath,
            'image_path' => 'C:\\Images'
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['pdf_path']);
});

test('guest cannot access file path settings', function () {
    $this->getJson('/api/file-path-settings')
        ->assertUnauthorized();
});
