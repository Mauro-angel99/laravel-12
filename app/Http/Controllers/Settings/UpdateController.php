<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UpdateController extends Controller
{
    /**
     * Esegue l'aggiornamento completo in modo sincrono.
     * Richiede fastcgi_read_timeout 600 in nginx.conf.
     */
    public function run(Request $request): JsonResponse
    {
        set_time_limit(0);

        $output = [];
        $errors = [];
        $projectRoot = base_path();

        putenv('HOME=/tmp');

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
            // 1. Fix safe.directory
            $runCmd('git config --global --add safe.directory ' . escapeshellarg($projectRoot), true);
            $runCmd('git config --global --add safe.directory \*', true);

            // 2. Rimuovi eventuali lock file residui
            $runCmd('rm -f .git/index.lock .git/MERGE_HEAD .git/CHERRY_PICK_HEAD', true);

            // 3. Interrompi eventuali operazioni in sospeso
            $runCmd('git merge --abort 2>/dev/null || true', true);
            $runCmd('git rebase --abort 2>/dev/null || true', true);

            // 4. Git fetch (scarica i commit senza applicarli)
            if (!$runCmd('git fetch origin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'git fetch fallito: impossibile raggiungere il repository remoto.',
                    'output'  => $output,
                    'errors'  => $errors,
                ], 500);
            }

            // 5. Rileva il branch corrente
            $branchLines = [];
            exec('cd ' . escapeshellarg($projectRoot) . ' && HOME=/tmp git rev-parse --abbrev-ref HEAD 2>&1', $branchLines, $branchExit);
            $branch = trim(implode('', $branchLines));
            if (empty($branch) || $branch === 'HEAD' || $branchExit !== 0) {
                $branch = 'main';
            }
            $output[] = ['cmd' => 'git branch', 'out' => "Branch corrente: {$branch}"];

            // 6. Reset hard al branch remoto (nessun conflitto possibile)
            if (!$runCmd("git reset --hard origin/{$branch}")) {
                return response()->json([
                    'success' => false,
                    'message' => "git reset --hard origin/{$branch} fallito.",
                    'output'  => $output,
                    'errors'  => $errors,
                ], 500);
            }

            // 7. Rimuovi file non tracciati che potrebbero interferire
            $runCmd('git clean -fd', true);

            // 8. npm build
            $nodeAvailable = !empty(shell_exec('which node 2>/dev/null'));

            if ($nodeAvailable) {
                $buildDir  = escapeshellarg($projectRoot . '/public/build');
                $buildBak  = escapeshellarg($projectRoot . '/public/build_bak');
                $runCmd("mv {$buildDir} {$buildBak} 2>/dev/null || true", true);
                $runCmd('npm install --prefer-offline');
                $buildOk = $runCmd('npm run build');
                if ($buildOk) {
                    $runCmd("rm -rf {$buildBak}", true);
                } else {
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
                        'out' => 'SKIP — Node.js non disponibile nel container.',
                    ];
                }
            }

            // 9. Artisan migrate
            try {
                Artisan::call('migrate', ['--force' => true]);
                $output[] = ['cmd' => 'artisan migrate', 'out' => trim(Artisan::output())];
            } catch (\Throwable $e) {
                $output[] = ['cmd' => 'artisan migrate', 'out' => $e->getMessage()];
                $errors[] = 'artisan migrate fallito: ' . $e->getMessage();
            }

            // 10. Artisan optimize:clear
            try {
                Artisan::call('optimize:clear');
                $output[] = ['cmd' => 'artisan optimize:clear', 'out' => trim(Artisan::output())];
            } catch (\Throwable $e) {
                $output[] = ['cmd' => 'artisan optimize:clear', 'out' => $e->getMessage()];
                $errors[] = 'artisan optimize:clear fallito: ' . $e->getMessage();
            }

            $hasErrors = !empty($errors);
            return response()->json([
                'success' => !$hasErrors,
                'message' => $hasErrors
                    ? 'Aggiornamento completato con errori.'
                    : 'Aggiornamento completato.',
                'output'  => $output,
                'errors'  => $errors,
            ], $hasErrors ? 500 : 200);
        } catch (\Throwable $e) {
            $output[] = ['cmd' => 'eccezione', 'out' => $e->getMessage()];
            return response()->json([
                'success' => false,
                'message' => 'Errore imprevisto: ' . $e->getMessage(),
                'output'  => $output,
                'errors'  => array_merge($errors, [$e->getMessage()]),
            ], 500);
        }
    }
}
