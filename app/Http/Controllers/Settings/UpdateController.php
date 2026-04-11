<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller
{
    private function statusFile(): string
    {
        return storage_path('app/update-status.json');
    }

    private function writeStatus(array $data): void
    {
        file_put_contents($this->statusFile(), json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Avvia l'aggiornamento in background e restituisce subito la risposta.
     */
    public function run(Request $request): JsonResponse
    {
        // Controlla se c'è già un aggiornamento in corso
        if (file_exists($this->statusFile())) {
            $current = json_decode(file_get_contents($this->statusFile()), true);
            if (isset($current['status']) && $current['status'] === 'running') {
                return response()->json([
                    'success' => true,
                    'message' => 'Aggiornamento già in corso...',
                    'async'   => true,
                ]);
            }
        }

        // Segna lo stato come "running"
        $this->writeStatus([
            'status'  => 'running',
            'step'    => 'Avvio aggiornamento...',
            'output'  => [],
            'errors'  => [],
            'started' => now()->toDateTimeString(),
        ]);

        // Lancia lo script di aggiornamento in background con nohup
        $php = PHP_BINARY ?: 'php';
        $artisan = base_path('artisan');
        $cmd = sprintf(
            'nohup %s %s app:run-update > /dev/null 2>&1 &',
            escapeshellarg($php),
            escapeshellarg($artisan)
        );
        exec($cmd);

        return response()->json([
            'success' => true,
            'message' => 'Aggiornamento avviato in background...',
            'async'   => true,
        ]);
    }

    /**
     * Restituisce lo stato corrente dell'aggiornamento (polling dal frontend).
     */
    public function status(Request $request): JsonResponse
    {
        if (!file_exists($this->statusFile())) {
            return response()->json([
                'status' => 'idle',
                'message' => 'Nessun aggiornamento in corso.',
            ]);
        }

        $data = json_decode(file_get_contents($this->statusFile()), true);
        return response()->json($data);
    }
}
