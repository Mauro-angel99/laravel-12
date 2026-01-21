<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobParameterValue extends Model
{
    protected $fillable = [
        'job_code',
        'art_code',
        'parameter_values',
    ];

    protected $casts = [
        'parameter_values' => 'array',
    ];
}
