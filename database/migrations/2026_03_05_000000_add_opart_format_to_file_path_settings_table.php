<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('file_path_settings', function (Blueprint $table) {
            $table->unsignedInteger('opart_total_chars')->nullable()->after('pdf_path');
            $table->unsignedInteger('opart_remove_before')->nullable()->after('opart_total_chars');
            $table->unsignedInteger('opart_remove_after')->nullable()->after('opart_remove_before');
        });
    }

    public function down(): void
    {
        Schema::table('file_path_settings', function (Blueprint $table) {
            $table->dropColumn(['opart_total_chars', 'opart_remove_before', 'opart_remove_after']);
        });
    }
};
