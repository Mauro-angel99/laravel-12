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

        try {

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

            // 6. npm build — prova Node direttamente nel container, altrimenti usa Docker
            $nodeAvailable = !empty(shell_exec('which node 2>/dev/null'));

            if ($nodeAvailable) {
                // Sposta il build corrente come backup, esegui il build, poi rimuovi il backup solo se ok
                $buildDir  = escapeshellarg($projectRoot . '/public/build');
                $buildBak  = escapeshellarg($projectRoot . '/public/build_bak');
                $runCmd("mv {$buildDir} {$buildBak} 2>/dev/null || true", true);
                $runCmd('npm install --prefer-offline');
                $buildOk = $runCmd('npm run build');
                if ($buildOk) {
                    $runCmd("rm -rf {$buildBak}", true);
                } else {
                    // Ripristina il backup se il build è fallito
                    $runCmd("rm -rf {$buildDir}; mv {$buildBak} {$buildDir} 2>/dev/null || true", true);
                }
            } else {
                $hostAppPath = env('HOST_APP_PATH');
                $dockerAvailable = !empty(shell_exec('which docker 2>/dev/null'));

                if ($hostAppPath && $dockerAvailable) {
                    $npmCmd = 'docker run --rm -v ' . escapeshellarg($hostAppPath . ':/app') . ' -w /app node:latest sh -c "mv /app/public/build /app/public/build_bak 2>/dev/null; npm install && npm run build && rm -rf /app/public/build_bak || (rm -rf /app/public/build; mv /app/public/build_bak /app/public/build)"';
                    $runCmd($npmCmd);
                } else {
                    $output[] = [
                        'cmd' => 'npm run build',
                        'out' => 'SKIP — Node.js non disponibile nel container. '
                            . 'Aggiungi nel Dockerfile: RUN apt-get install -y nodejs npm '
                            . 'oppure usa nvm per installare Node.',
                    ];
                }
            }

            // 5. Artisan migrate
            try {
                Artisan::call('migrate', ['--force' => true]);
                $output[] = ['cmd' => 'artisan migrate', 'out' => trim(Artisan::output())];
            } catch (\Throwable $e) {
                $output[] = ['cmd' => 'artisan migrate', 'out' => $e->getMessage()];
                $errors[] = 'artisan migrate fallito: ' . $e->getMessage();
            }

            // 6. Artisan optimize:clear
            try {
                Artisan::call('optimize:clear');
                $output[] = ['cmd' => 'artisan optimize:clear', 'out' => trim(Artisan::output())];
            } catch (\Throwable $e) {
                $output[] = ['cmd' => 'artisan optimize:clear', 'out' => $e->getMessage()];
                $errors[] = 'artisan optimize:clear fallito: ' . $e->getMessage();
            }

            $npmDone = $nodeAvailable || (!empty(env('HOST_APP_PATH')) && !empty(shell_exec('which docker 2>/dev/null')));

            $hasErrors = !empty($errors);
            $message = $hasErrors
                ? 'Aggiornamento completato con errori. Controlla i log per i dettagli.'
                : ($npmDone
                    ? 'Aggiornamento completato (git pull + npm run build + migrate + cache clear).'
                    : 'Aggiornamento parziale completato. npm run build saltato: controlla il log per i dettagli.');

            return response()->json([
                'success' => !$hasErrors,
                'npmDone' => $npmDone,
                'message' => $message,
                'output'  => $output,
                'errors'  => $errors,
            ], $hasErrors ? 500 : 200);
        } catch (\Throwable $e) {
            $output[] = ['cmd' => 'eccezione', 'out' => $e->getMessage()];
            return response()->json([
                'success' => false,
                'message' => 'Errore imprevisto durante l\'aggiornamento: ' . $e->getMessage(),
                'output'  => $output,
                'errors'  => array_merge($errors, [$e->getMessage()]),
            ], 500);
        }
    }
}
