<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehousePosition extends Model
{
    protected $fillable = [
        'warehouse_position',
        'started',
    ];

    protected $casts = [
        'started' => 'boolean',
    ];

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}
