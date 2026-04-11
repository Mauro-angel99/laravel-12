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
        Schema::create('bom_parameter_values', function (Blueprint $table) {
            $table->id();
            $table->string('dllav');
            $table->string('dbart');
            $table->json('parameter_values')->nullable();
            $table->timestamps();

            $table->unique(['dllav', 'dbart']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_parameter_values');
    }
};
