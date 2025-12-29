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
        Schema::create('warehouse_positions', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_area', 50)->nullable();
            $table->string('warehouse_position', 50);
            $table->timestamps();
            
            // Indice unico su area + posizione
            $table->unique(['warehouse_area', 'warehouse_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_positions');
    }
};
