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
        Schema::create('work_phase_images', function (Blueprint $table) {
            $table->id();
            $table->string('fllav', 50)->index()->comment('Codice Lavorazione');
            $table->string('opart', 50)->index()->comment('Codice Articolo');
            $table->string('file_name')->comment('Nome file originale');
            $table->string('file_path')->comment('Percorso file sul disco');
            $table->string('mime_type', 50)->comment('Tipo MIME del file');
            $table->unsignedInteger('file_size')->comment('Dimensione file in bytes');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            // Indice composito per ricerca veloce
            $table->index(['fllav', 'opart']);
            
            // Foreign key per tracciare chi ha caricato
            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_phase_images');
    }
};
