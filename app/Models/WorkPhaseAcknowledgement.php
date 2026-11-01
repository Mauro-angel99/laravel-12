<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkPhaseAcknowledgement extends Model
{
    protected $fillable = [
        'opart_padre',
        'opart',
        'flass',
        'fllav',
        'idopr',
        'user_id',
        'user_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione con l'operatore che ha registrato la presa visione
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
