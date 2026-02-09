<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';

    public $timestamps = true;

    protected $fillable = [
        'warehouse_position_id',
        'product_code',
        'product_description',
        'production_order',
        'quantity',
        'notes',
        'pending',
        'pending_code',
        'created_by',
        'updated_by',
        'received_at',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'pending' => 'boolean',
        'received_at' => 'datetime',
        'quantity' => 'decimal:2',
        'warehouse_position_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    public function warehousePosition(): BelongsTo
    {
        return $this->belongsTo(WarehousePosition::class, 'warehouse_position_id');
    }

    // Alias per retrocompatibilità
    public function position(): BelongsTo
    {
        return $this->warehousePosition();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
