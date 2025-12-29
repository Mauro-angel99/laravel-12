<?php

namespace App\Console\Commands;

use App\Models\WarehousePosition;
use Illuminate\Console\Command;

class CleanEmptyPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:clean-empty-positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina le posizioni di magazzino che non hanno merci associate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Ricerca posizioni vuote...');

        $emptyPositions = WarehousePosition::doesntHave('warehouses')->get();
        $count = $emptyPositions->count();

        if ($count === 0) {
            $this->info('Nessuna posizione vuota trovata.');
            return 0;
        }

        $this->info("Trovate {$count} posizioni vuote. Eliminazione in corso...");

        foreach ($emptyPositions as $position) {
            $this->line("- Eliminazione posizione: {$position->warehouse_position}");
            $position->delete();
        }

        $this->info("Eliminate {$count} posizioni vuote con successo!");
        return 0;
    }
}
