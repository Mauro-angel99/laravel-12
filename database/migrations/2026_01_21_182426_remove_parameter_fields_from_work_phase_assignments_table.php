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
        Schema::table('work_phase_assignments', function (Blueprint $table) {
            $table->dropForeign(['work_parameter_id']);
            $table->dropColumn(['work_parameter_id', 'parameter_values']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_phase_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('work_parameter_id')->nullable()->after('notes');
            $table->json('parameter_values')->nullable()->after('work_parameter_id');
            $table->foreign('work_parameter_id')->references('id')->on('work_parameters')->onDelete('set null');
        });
    }
};
