<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehousePosition extends Model
{
    protected $fillable = [
        'warehouse_position',
        'started',
        'quantity',
    ];

    protected $casts = [
        'started' => 'boolean',
        'quantity' => 'decimal:3',
    ];

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}
