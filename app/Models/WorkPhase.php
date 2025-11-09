<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkPhase extends Model
{
    protected $table = 'A01_ORD_FAS'; // Nome della tabella su SQL Server
    protected $primaryKey = 'RECORD_ID'; // Chiave primaria
    public $timestamps = false; // Non ci sono created_at / updated_at nella tabella

    // Se vuoi proteggere i campi, puoi usare $fillable
    protected $fillable = [
        'FLASS', 'IDOPR', 'FLSEQ', 'FLLAV', 'FLDES', 
        'FLQTA', 'FLQTB', 'FLQTD', 'FLCON'
    ];

    /**
     * Relazione con gli assignment
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(WorkPhaseAssignment::class, 'work_phase_id');
    }
}
