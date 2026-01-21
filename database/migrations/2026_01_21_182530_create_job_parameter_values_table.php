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
        Schema::create('job_parameter_values', function (Blueprint $table) {
            $table->id();
            $table->string('job_code');
            $table->string('art_code');
            $table->json('parameter_values')->nullable();
            $table->timestamps();
            
            // Indice univoco per evitare duplicati
            $table->unique(['job_code', 'art_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_parameter_values');
    }
};
