<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPhaseAssignment extends Model
{
    // Abilita automaticamente created_at e updated_at
    public $timestamps = true;

    // Colonne che possono essere assegnate in massa
    protected $fillable = [
        'work_phase_id',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'completed_at',
        'status',
        'notes',
    ];

    // Proteggi campi sensibili
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    // Cast automatici
    protected $casts = [
        'work_phase_id' => 'integer',
        'assigned_to' => 'integer',
        'assigned_by' => 'integer',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relazioni
    public function workPhase(): BelongsTo
    {
        return $this->belongsTo(WorkPhase::class, 'work_phase_id', 'RECORD_ID');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Alias per retrocompatibilità
    public function assignedUser(): BelongsTo
    {
        return $this->user();
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Alias per retrocompatibilità
    public function assignedBy(): BelongsTo
    {
        return $this->assignedByUser();
    }
}
