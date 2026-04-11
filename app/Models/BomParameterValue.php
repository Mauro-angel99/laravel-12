<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomParameterValue extends Model
{
    protected $fillable = [
        'dllav',
        'dbart',
        'parameter_values',
    ];

    protected $casts = [
        'parameter_values' => 'array',
    ];
}
