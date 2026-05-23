<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_path_settings', function (Blueprint $table) {
            $table->string('heat_search', 100)->nullable()->after('opart_remove_after');
            $table->string('heat_replace', 100)->nullable()->after('heat_search');
        });
    }

    public function down(): void
    {
        Schema::table('file_path_settings', function (Blueprint $table) {
            $table->dropColumn(['heat_search', 'heat_replace']);
        });
    }
};
