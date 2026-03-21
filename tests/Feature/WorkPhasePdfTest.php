<?php

use App\Models\FilePathSetting;
use App\Models\User;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->pdfDirectory = storage_path('framework/testing/pdfs/' . uniqid('pdf_', true));

    File::ensureDirectoryExists($this->pdfDirectory);
});

afterEach(function () {
    File::deleteDirectory($this->pdfDirectory);
});

test('serves a pdf from the configured directory', function () {
    FilePathSetting::create([
        'pdf_path' => $this->pdfDirectory,
    ]);

    File::put($this->pdfDirectory . DIRECTORY_SEPARATOR . '1BP010860.pdf', "%PDF-1.4\n%%EOF");

    $this->actingAs($this->user)
        ->get('/api/work-phase-pdf?opart=1BP010860')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('uses opart formatting settings when resolving the pdf name', function () {
    FilePathSetting::create([
        'pdf_path' => $this->pdfDirectory,
        'opart_total_chars' => 9,
        'opart_remove_before' => 2,
        'opart_remove_after' => 2,
    ]);

    File::put($this->pdfDirectory . DIRECTORY_SEPARATOR . 'CDEFGHI.pdf', "%PDF-1.4\n%%EOF");

    $this->actingAs($this->user)
        ->get('/api/work-phase-pdf?opart=ABCDEFGHIJK')
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('returns 404 when the pdf is missing', function () {
    FilePathSetting::create([
        'pdf_path' => $this->pdfDirectory,
    ]);

    $this->actingAs($this->user)
        ->get('/api/work-phase-pdf?opart=NOT_FOUND')
        ->assertNotFound();
});
