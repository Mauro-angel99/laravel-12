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
        Schema::table('warehouse_positions', function (Blueprint $table) {
            $table->dropUnique(['warehouse_area', 'warehouse_position']);
            $table->dropColumn('warehouse_area');
            $table->unique('warehouse_position');
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_positions', function (Blueprint $table) {
            $table->dropUnique(['warehouse_position']);
            $table->string('warehouse_area', 50)->nullable()->after('id');
            $table->unique(['warehouse_area', 'warehouse_position']);
        });

        Schema::table('warehouses', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(0)->after('production_order');
        });
    }
};
