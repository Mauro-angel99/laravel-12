<?php

namespace App\Http\Controllers;

use App\Models\FilePathSetting;
use App\Http\Requests\UpdateFilePathSettingsRequest;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FilePathSettingController extends Controller
{
    public function __construct(
        private AuditLogger $auditLogger
    ) {}

    /**
     * Display the file path settings.
     */
    public function index(): JsonResponse
    {
        try {
            $setting = FilePathSetting::first();

            return response()->json($setting ?? [
                'pdf_path' => '',
                'image_path' => '',
            ]);
        } catch (\Exception $e) {
            Log::error('File path settings fetch error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Errore nel caricamento delle impostazioni'
            ], 500);
        }
    }

    /**
     * Update the file path settings.
     */
    public function update(UpdateFilePathSettingsRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            $setting = FilePathSetting::first();

            if (!$setting) {
                $setting = FilePathSetting::create($validated);
                $action = 'created';
            } else {
                $setting->update($validated);
                $action = 'updated';
            }

            $this->auditLogger->logSecurity("File path settings {$action}", [
                'settings' => $validated
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Percorsi aggiornati con successo',
                'data' => $setting,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->auditLogger->logCritical('File path settings update failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);
            
            return response()->json([
                'error' => 'Errore durante l\'aggiornamento dei percorsi'
            ], 500);
        }
    }
}
