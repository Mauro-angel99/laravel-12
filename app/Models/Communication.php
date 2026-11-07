<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Communication extends Model
{
    protected $fillable = [
        'opart_padre',
        'opart',
        'flass',
        'fllav',
        'idopr',
        'message',
        'user_id',
        'user_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione con l'utente che ha scritto la comunicazione
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Utenti a cui i moderatori hanno assegnato la comunicazione
     */
    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('read_at')
            ->withTimestamps();
    }

    /**
     * L'autore e i moderatori possono modificare/eliminare la comunicazione
     */
    public function canBeManagedBy(?User $user): bool
    {
        return $user && ($user->id === $this->user_id || $user->is_moderator);
    }
}
