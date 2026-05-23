<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['dimension_x', 'dimension_y']);
            $table->string('format', 100)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('format');
            $table->decimal('dimension_x', 10, 3)->nullable()->after('notes');
            $table->decimal('dimension_y', 10, 3)->nullable()->after('dimension_x');
        });
    }
};
