<?php

namespace App\Http\Controllers;

use App\Models\FilePathSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WorkPhasePdfController extends Controller
{
    /**
     * Verifica se il PDF esiste senza restituire il file (evita problemi con HEAD in produzione).
     */
    public function check(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'opart' => ['required', 'string', 'max:255'],
        ]);

        $opart = trim($validated['opart']);

        if ($opart === '' || str_contains($opart, '/') || str_contains($opart, '\\')) {
            return response()->json(['exists' => false]);
        }

        $setting  = FilePathSetting::first();
        $basePath = trim((string) ($setting?->pdf_path ?? ''));

        if ($basePath === '') {
            Log::warning('PDF check: pdf_path non configurato');
            return response()->json(['exists' => false]);
        }

        foreach ($this->buildCandidateNames($opart, $setting) as $fileName) {
            $pdfPath = rtrim($basePath, "\\/") . DIRECTORY_SEPARATOR . $fileName;
            if (is_file($pdfPath)) {
                return response()->json(['exists' => true]);
            }
        }

        Log::info('PDF check: non trovato', ['opart' => $opart, 'pdf_path' => $basePath]);
        return response()->json(['exists' => false]);
    }

    public function show(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'opart' => ['required', 'string', 'max:255'],
        ]);

        $opart = trim($validated['opart']);

        if ($opart === '' || str_contains($opart, '/') || str_contains($opart, '\\')) {
            abort(404);
        }

        $setting = FilePathSetting::first();
        $basePath = trim((string) ($setting?->pdf_path ?? ''));

        if ($basePath === '') {
            abort(404);
        }

        foreach ($this->buildCandidateNames($opart, $setting) as $fileName) {
            $pdfPath = rtrim($basePath, "\\/") . DIRECTORY_SEPARATOR . $fileName;

            if (is_file($pdfPath)) {
                return response()->file($pdfPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                ]);
            }
        }

        Log::info('PDF non trovato per articolo', [
            'opart' => $opart,
            'pdf_path' => $basePath,
        ]);

        abort(404);
    }

    /**
     * @return array<int, string>
     */
    private function buildCandidateNames(string $opart, ?FilePathSetting $setting): array
    {
        $formatted = $this->formatOpart($opart, $setting);

        return array_values(array_unique([
            $formatted . '.pdf',
            $opart . '.pdf',
        ]));
    }

    private function formatOpart(string $value, ?FilePathSetting $setting): string
    {
        $totalChars = $setting?->opart_total_chars;

        if (!$totalChars) {
            return $value;
        }

        if (strlen($value) <= $totalChars) {
            return $value;
        }

        $removeBefore = $setting?->opart_remove_before ?? 0;
        $removeAfter = $setting?->opart_remove_after ?? 0;

        return substr($value, $removeBefore, strlen($value) - $removeBefore - $removeAfter);
    }
}
