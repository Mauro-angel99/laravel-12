<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller
{
    /**
     * Esegue l'aggiornamento completo:
     * git stash → git pull → git stash pop → npm ci && npm run build (se Node disponibile) → migrate → optimize:clear
     */
    public function run(Request $request): JsonResponse
    {
        $output = [];
        $errors = [];

        $projectRoot = base_path();

        // www-data non ha home scrivibile: usiamo /tmp come HOME per git
        putenv('HOME=/tmp');

        // Helper: esegue un comando shell, raccoglie output e codice di uscita
        $runCmd = function (string $cmd, bool $ignoreError = false) use ($projectRoot, &$output, &$errors): bool {
            $fullCmd = 'cd ' . escapeshellarg($projectRoot) . ' && HOME=/tmp ' . $cmd . ' 2>&1';
            exec($fullCmd, $lines, $exitCode);
            $output[] = ['cmd' => $cmd, 'out' => implode("\n", $lines)];
            if ($exitCode !== 0 && !$ignoreError) {
                $errors[] = "Comando fallito (exit {$exitCode}): {$cmd}";
                return false;
            }
            return true;
        };

        // 1. Fix safe.directory (necessario quando la cartella è montata via Docker volume)
        $runCmd('git config --global --add safe.directory ' . escapeshellarg($projectRoot), true);
        $runCmd('git config --global --add safe.directory \*', true);

        // 3. Git stash (ignora errori: potrebbe non esserci nulla da salvare)
        $runCmd('git stash', true);

        // 4. Git pull
        if (!$runCmd('git pull')) {
            return response()->json([
                'success' => false,
                'message' => 'git pull fallito. Controlla i log per i dettagli.',
                'output'  => $output,
                'errors'  => $errors,
            ], 500);
        }

        // 5. Git stash pop (ignora errori: stash potrebbe essere vuoto)
        $runCmd('git stash pop', true);

        // 6. npm build — tramite container Node dedicato (come: docker run --rm -v HOST_PATH:/app node:latest ...)
        $hostAppPath = env('HOST_APP_PATH');
        $dockerAvailable = !empty(shell_exec('which docker 2>/dev/null'));

        if ($hostAppPath && $dockerAvailable) {
            // Stesso comando usato manualmente dall'host
            $npmCmd = 'docker run --rm -v ' . escapeshellarg($hostAppPath . ':/app') . ' -w /app node:latest sh -c "npm install && npm run build"';
            $runCmd($npmCmd);
        } elseif (!$dockerAvailable) {
            $output[] = [
                'cmd' => 'npm run build',
                'out' => 'SKIP — Docker CLI non disponibile nel container. '
                    . 'Aggiungi al docker-compose.yml sotto il servizio app: '
                    . 'volumes: [/var/run/docker.sock:/var/run/docker.sock] '
                    . 'e installa docker-cli nel Dockerfile.',
            ];
        } else {
            $output[] = [
                'cmd' => 'npm run build',
                'out' => 'SKIP — HOST_APP_PATH non impostato nel file .env. '
                    . 'Aggiungi: HOST_APP_PATH=C:/prod/src',
            ];
        }

        // 5. Artisan migrate
        Artisan::call('migrate', ['--force' => true]);
        $output[] = ['cmd' => 'artisan migrate', 'out' => trim(Artisan::output())];

        // 6. Artisan optimize:clear
        Artisan::call('optimize:clear');
        $output[] = ['cmd' => 'artisan optimize:clear', 'out' => trim(Artisan::output())];

        $npmDone = $hostAppPath && $dockerAvailable;
        $message = $npmDone
            ? 'Aggiornamento completato (git pull + npm run build + migrate + cache clear).'
            : 'Aggiornamento parziale completato. npm run build saltato: controlla il log per i dettagli.';

        return response()->json([
            'success' => true,
            'npmDone' => $npmDone,
            'message' => $message,
            'output'  => $output,
            'errors'  => $errors,
        ]);
    }
}