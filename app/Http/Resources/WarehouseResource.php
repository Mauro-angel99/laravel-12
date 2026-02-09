<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_code' => $this->product_code,
            'product_description' => $this->product_description,
            'production_order' => $this->production_order,
            'quantity' => $this->quantity,
            'pending' => $this->pending,
            'pending_code' => $this->pending_code,
            'received_at' => $this->received_at?->toIso8601String(),
            'notes' => $this->notes,
            'warehouse_position' => $this->whenLoaded('warehousePosition', function () {
                return [
                    'id' => $this->warehousePosition->id,
                    'warehouse_position' => $this->warehousePosition->warehouse_position
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String()
        ];
    }
}
