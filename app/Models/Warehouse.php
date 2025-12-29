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
        'notes',
        'pending',
        'pending_code',
    ];

    protected $casts = [
        'pending' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(WarehousePosition::class, 'warehouse_position_id');
    }
}
