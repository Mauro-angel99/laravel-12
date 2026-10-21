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
        Schema::create('article_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('cdart', 50)->index()->comment('Codice Articolo');
            $table->text('note')->comment('Nota dell\'alert');
            $table->unsignedBigInteger('signed_by')->nullable()->comment('Utente che firma l\'alert');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Utente che ha creato l\'alert');
            $table->timestamps();

            $table->foreign('signed_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('created_by')
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
        Schema::dropIfExists('article_alerts');
    }
};
