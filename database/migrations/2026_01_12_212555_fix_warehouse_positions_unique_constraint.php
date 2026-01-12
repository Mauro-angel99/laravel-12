<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouse_positions', function (Blueprint $table) {
            // Aggiungi indice unico solo su warehouse_position se non esiste
            if (!$this->hasIndex('warehouse_positions', 'warehouse_positions_warehouse_position_unique')) {
                $table->unique('warehouse_position');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_positions', function (Blueprint $table) {
            // Rimuovi indice unico su warehouse_position
            if ($this->hasIndex('warehouse_positions', 'warehouse_positions_warehouse_position_unique')) {
                $table->dropUnique(['warehouse_position']);
            }
        });
    }
    
    /**
     * Check if an index exists
     */
    private function hasIndex($table, $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        return !empty($indexes);
    }
};
