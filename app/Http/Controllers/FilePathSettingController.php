<?php

namespace App\Http\Controllers;

use App\Models\FilePathSetting;
use App\Http\Requests\UpdateFilePathSettingsRequest;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                'opart_total_chars' => null,
                'opart_remove_before' => null,
                'opart_remove_after' => null,
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
                'message' => 'Impostazioni aggiornate con successo',
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

    /**
     * Update only formatting settings.
     */
    public function updateFormatting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opart_total_chars'  => ['nullable', 'integer', 'min:0'],
            'opart_remove_before' => ['nullable', 'integer', 'min:0'],
            'opart_remove_after'  => ['nullable', 'integer', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            $setting = FilePathSetting::first();

            if (!$setting) {
                $setting = FilePathSetting::create($validated);
            } else {
                $setting->update($validated);
            }

            $this->auditLogger->logSecurity('Formatting settings updated', ['settings' => $validated]);

            DB::commit();

            return response()->json([
                'message' => 'Formattazione aggiornata con successo',
                'data' => $setting,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Formatting settings update failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Errore durante l\'aggiornamento della formattazione'
            ], 500);
        }
    }

    /**
     * Serve a PDF file from the configured pdf_path.
     */
    public function servePdf(Request $request): StreamedResponse|JsonResponse
    {
        $opart = $request->query('opart', '');

        // Prevent path traversal attacks - only allow safe filename characters
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $opart)) {
            return response()->json(['error' => 'Codice articolo non valido'], 400);
        }

        $fileUrl = 'http://host.docker.internal:8082/' . urlencode($opart) . '.pdf';

        $ch = curl_init($fileUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $pdfContent = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$pdfContent) {
            return response()->json(['error' => 'PDF non trovato'], 404);
        }

        return response()->stream(function () use ($pdfContent) {
            echo $pdfContent;
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $opart . '.pdf"',
        ]);
    }
}
