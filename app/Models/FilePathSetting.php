<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilePathSetting extends Model
{
    protected $fillable = [
        'pdf_path',
        'opart_total_chars',
        'opart_remove_before',
        'opart_remove_after',
    ];

    protected $casts = [
        'opart_total_chars' => 'integer',
        'opart_remove_before' => 'integer',
        'opart_remove_after' => 'integer',
    ];
}
