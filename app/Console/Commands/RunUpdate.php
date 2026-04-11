<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunUpdate extends Command
{
    protected $signature = 'app:run-update';
    protected $description = 'Esegue l\'aggiornamento completo in background';

    private function statusFile(): string
    {
        return storage_path('app/update-status.json');
    }

    private function writeStatus(array $data): void
    {
        file_put_contents($this->statusFile(), json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function handle(): int
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

        $updateStep = function (string $step) use (&$output, &$errors) {
            $this->writeStatus([
                'status'  => 'running',
                'step'    => $step,
                'output'  => $output,
                'errors'  => $errors,
            ]);
        };

        try {
            // 1. Fix safe.directory
            $updateStep('Configurazione git...');
            $runCmd('git config --global --add safe.directory ' . escapeshellarg($projectRoot), true);
            $runCmd('git config --global --add safe.directory \*', true);

            // 2. Git stash
            $updateStep('Git stash...');
            $runCmd('git stash', true);

            // 3. Git pull
            $updateStep('Git pull...');
            if (!$runCmd('git pull')) {
                $this->writeStatus([
                    'status'  => 'error',
                    'step'    => 'Git pull fallito',
                    'output'  => $output,
                    'errors'  => $errors,
                ]);
                return 1;
            }

            // 4. Git stash pop
            $updateStep('Git stash pop...');
            $runCmd('git stash pop', true);

            // 5. npm build
            $nodeAvailable = !empty(shell_exec('which node 2>/dev/null'));

            if ($nodeAvailable) {
                $updateStep('npm install...');
                $buildDir  = escapeshellarg($projectRoot . '/public/build');
                $buildBak  = escapeshellarg($projectRoot . '/public/build_bak');
                $runCmd("mv {$buildDir} {$buildBak} 2>/dev/null || true", true);
                $runCmd('npm install --prefer-offline');

                $updateStep('npm run build...');
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
                    $updateStep('npm build (Docker)...');
                    $npmCmd = 'docker run --rm -v ' . escapeshellarg($hostAppPath . ':/app') . ' -w /app node:latest sh -c "mv /app/public/build /app/public/build_bak 2>/dev/null; npm install && npm run build && rm -rf /app/public/build_bak || (rm -rf /app/public/build; mv /app/public/build_bak /app/public/build)"';
                    $runCmd($npmCmd);
                } else {
                    $output[] = [
                        'cmd' => 'npm run build',
                        'out' => 'SKIP — Node.js non disponibile nel container.',
                    ];
                }
            }

            // 6. Artisan migrate
            $updateStep('Migrazione database...');
            try {
                Artisan::call('migrate', ['--force' => true]);
                $output[] = ['cmd' => 'artisan migrate', 'out' => trim(Artisan::output())];
            } catch (\Throwable $e) {
                $output[] = ['cmd' => 'artisan migrate', 'out' => $e->getMessage()];
                $errors[] = 'artisan migrate fallito: ' . $e->getMessage();
            }

            // 7. Artisan optimize:clear
            $updateStep('Pulizia cache...');
            try {
                Artisan::call('optimize:clear');
                $output[] = ['cmd' => 'artisan optimize:clear', 'out' => trim(Artisan::output())];
            } catch (\Throwable $e) {
                $output[] = ['cmd' => 'artisan optimize:clear', 'out' => $e->getMessage()];
                $errors[] = 'artisan optimize:clear fallito: ' . $e->getMessage();
            }

            // Scrivi stato finale
            $hasErrors = !empty($errors);
            $this->writeStatus([
                'status'  => $hasErrors ? 'error' : 'done',
                'step'    => $hasErrors ? 'Completato con errori' : 'Aggiornamento completato!',
                'output'  => $output,
                'errors'  => $errors,
            ]);

            return $hasErrors ? 1 : 0;
        } catch (\Throwable $e) {
            $output[] = ['cmd' => 'eccezione', 'out' => $e->getMessage()];
            $this->writeStatus([
                'status'  => 'error',
                'step'    => 'Errore imprevisto',
                'output'  => $output,
                'errors'  => array_merge($errors, [$e->getMessage()]),
            ]);
            return 1;
        }
    }
}
