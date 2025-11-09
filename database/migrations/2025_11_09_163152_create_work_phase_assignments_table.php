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
        Schema::create('work_phase_assignments', function (Blueprint $table) {
            $table->id();

            // ID della fase di lavoro proveniente dal DB esterno
            $table->unsignedBigInteger('work_phase_id');

            // Utente a cui è assegnata la fase
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');

            // Utente che ha assegnato la fase
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');

            $table->string('status', 50)->default('pending'); // pending, in_progress, completed, blocked
            $table->integer('priority')->default(3);
            $table->text('notes')->nullable();

            $table->timestamp('start_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_phase_assignments');
    }
};
