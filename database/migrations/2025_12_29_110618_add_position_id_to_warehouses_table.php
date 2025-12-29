<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verifica se la colonna esiste già
        if (!Schema::hasColumn('warehouses', 'warehouse_position_id')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_position_id')->nullable()->after('id');
            });
        }

        // Verifica se ci sono warehouse senza posizione assegnata
        $warehousesWithoutPosition = DB::table('warehouses')
            ->whereNull('warehouse_position_id')
            ->get();

        if ($warehousesWithoutPosition->count() > 0) {
            // Crea una posizione di default per i record senza posizione
            $defaultPositionId = DB::table('warehouse_positions')->insertGetId([
                'warehouse_area' => null,
                'warehouse_position' => 'NON_SPECIFICATA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Aggiorna tutti i warehouse senza posizione
            DB::table('warehouses')
                ->whereNull('warehouse_position_id')
                ->update(['warehouse_position_id' => $defaultPositionId]);
        }

        // Verifica se warehouse_area e warehouse_position esistono ancora
        $hasOldColumns = Schema::hasColumn('warehouses', 'warehouse_area') && 
                        Schema::hasColumn('warehouses', 'warehouse_position');

        Schema::table('warehouses', function (Blueprint $table) use ($hasOldColumns) {
            // Rendi la colonna non nullable
            $table->unsignedBigInteger('warehouse_position_id')->nullable(false)->change();
            
            // Aggiungi la foreign key se non esiste
            if (!DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'warehouses' 
                AND CONSTRAINT_NAME = 'warehouses_warehouse_position_id_foreign'")) {
                $table->foreign('warehouse_position_id')
                      ->references('id')
                      ->on('warehouse_positions')
                      ->onDelete('cascade');
            }
            
            // Rimuovi i vecchi campi se esistono ancora
            if ($hasOldColumns) {
                $table->dropColumn(['warehouse_area', 'warehouse_position']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['warehouse_position_id']);
            $table->dropColumn('warehouse_position_id');
            
            $table->string('warehouse_area', 50)->nullable();
            $table->string('warehouse_position', 50);
        });
    }
};
