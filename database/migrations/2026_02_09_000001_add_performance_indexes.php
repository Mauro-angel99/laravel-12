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
        $this->addIndexIfNotExists('work_phase_assignments', 'status', 'idx_wpa_status');
        $this->addIndexIfNotExists('work_phase_assignments', 'work_phase_id', 'idx_wpa_work_phase_id');
        $this->addIndexIfNotExists('work_phase_assignments', 'completed_at', 'idx_wpa_completed_at');
        $this->addIndexIfNotExists('work_phase_assignments', 'start_at', 'idx_wpa_start_at');
        $this->addIndexIfNotExists('work_phase_assignments', 'due_at', 'idx_wpa_due_at');

        $this->addIndexIfNotExists('warehouses', 'warehouse_position_id', 'idx_warehouses_position_id');
        $this->addIndexIfNotExists('warehouses', 'received_at', 'idx_warehouses_received_at');
        $this->addIndexIfNotExists('warehouses', 'product_code', 'idx_warehouses_product_code');
        $this->addIndexIfNotExists('warehouses', 'production_order', 'idx_warehouses_production_order');
        
        // Composite index for pending items
        $this->addCompositeIndexIfNotExists('warehouses', ['pending', 'pending_code'], 'idx_warehouses_pending');

        if (Schema::hasTable('work_parameters')) {
            $this->addIndexIfNotExists('work_parameters', 'name', 'idx_work_parameters_name');
        }

        if (Schema::hasTable('warehouse_positions')) {
            $this->addIndexIfNotExists('warehouse_positions', 'warehouse_position', 'idx_warehouse_positions_position');
        }
    }

    /**
     * Add index if it doesn't already exist.
     */
    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        $exists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        
        if (empty($exists)) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName) {
                $blueprint->index($column, $indexName);
            });
        }
    }

    /**
     * Add composite index if it doesn't already exist.
     */
    private function addCompositeIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        $exists = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        
        if (empty($exists)) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'work_phase_assignments' => ['idx_wpa_status', 'idx_wpa_work_phase_id', 'idx_wpa_completed_at', 'idx_wpa_start_at', 'idx_wpa_due_at'],
            'warehouses' => ['idx_warehouses_position_id', 'idx_warehouses_pending', 'idx_warehouses_received_at', 'idx_warehouses_product_code', 'idx_warehouses_production_order']
        ];

        foreach ($indexes as $table => $tableIndexes) {
            Schema::table($table, function (Blueprint $blueprint) use ($tableIndexes) {
                foreach ($tableIndexes as $index) {
                    try {
                        $blueprint->dropIndex($index);
                    } catch (\Exception $e) {
                        // Index might not exist, skip
                    }
                }
            });
        }

        if (Schema::hasTable('work_parameters')) {
            Schema::table('work_parameters', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_work_parameters_name');
                } catch (\Exception $e) {
                    // Skip if not exists
                }
            });
        }

        if (Schema::hasTable('warehouse_positions')) {
            Schema::table('warehouse_positions', function (Blueprint $table) {
                try {
                    $table->dropIndex('idx_warehouse_positions_position');
                } catch (\Exception $e) {
                    // Skip if not exists
                }
            });
        }
    }
};
