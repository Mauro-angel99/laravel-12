<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPhaseAssignment extends Model
{
    // Abilita automaticamente created_at e updated_at
    public $timestamps = true;
    
    // Colonne che possono essere assegnate in massa
    protected $fillable = [
        'work_phase_id',
        'assigned_to',
        'assigned_by',
        'status',
        'notes',
    ];
}
