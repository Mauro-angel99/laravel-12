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
        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->string('opart_padre', 50)->nullable()->index()->comment('Articolo padre');
            $table->string('opart', 50)->nullable()->index()->comment('Codice articolo');
            $table->string('flass', 50)->nullable()->comment('FLASS');
            $table->string('fllav', 50)->nullable()->comment('FLLAV');
            $table->string('idopr', 50)->nullable()->index()->comment('IDOPR');
            $table->text('message')->comment('Testo della comunicazione');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Utente che ha scritto la comunicazione');
            $table->string('user_name')->nullable()->comment('Nome utente al momento dell\'invio');
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });

        // Destinatari assegnati dai moderatori
        Schema::create('communication_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('communication_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('read_at')->nullable()->comment('Data di lettura da parte del destinatario');
            $table->timestamps();

            $table->unique(['communication_id', 'user_id']);

            $table->foreign('communication_id')
                  ->references('id')
                  ->on('communications')
                  ->cascadeOnDelete();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_user');
        Schema::dropIfExists('communications');
    }
};
