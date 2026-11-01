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
        Schema::create('work_phase_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->string('opart_padre', 50)->nullable()->index()->comment('Articolo padre');
            $table->string('opart', 50)->nullable()->index()->comment('Codice articolo');
            $table->string('flass', 50)->nullable()->comment('FLASS');
            $table->string('fllav', 50)->nullable()->comment('FLLAV');
            $table->string('idopr', 50)->nullable()->index()->comment('IDOPR');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Operatore che ha registrato l\'inizio lavoro');
            $table->string('user_name')->nullable()->comment('Nome operatore al momento della registrazione');
            $table->timestamps();

            $table->foreign('user_id')
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
        Schema::dropIfExists('work_phase_acknowledgements');
    }
};
