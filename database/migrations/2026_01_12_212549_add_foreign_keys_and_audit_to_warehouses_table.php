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
        Schema::table('warehouses', function (Blueprint $table) {
            // Aggiungi campi per audit trail solo se non esistono
            if (!Schema::hasColumn('warehouses', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('pending_code');
            }
            if (!Schema::hasColumn('warehouses', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('warehouses', 'received_at')) {
                $table->timestamp('received_at')->nullable()->after('updated_by');
            }
        });
        
        // Aggiungi foreign keys in una seconda chiamata per evitare problemi
        Schema::table('warehouses', function (Blueprint $table) {
            // Aggiungi foreign keys per audit
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            // Rimuovi foreign keys
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['created_by']);
        });
        
        Schema::table('warehouses', function (Blueprint $table) {
            // Rimuovi colonne
            if (Schema::hasColumn('warehouses', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('warehouses', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('warehouses', 'received_at')) {
                $table->dropColumn('received_at');
            }
        });
    }
};
