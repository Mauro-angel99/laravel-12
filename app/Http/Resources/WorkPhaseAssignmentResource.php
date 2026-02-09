<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkPhaseAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_phase_id' => $this->work_phase_id,
            'assigned_to' => $this->assigned_to,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'status' => $this->status,
            'notes' => $this->notes,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email
                ];
            }),
            'work_phase' => $this->whenLoaded('workPhase', function () {
                return new WorkPhaseResource($this->workPhase);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String()
        ];
    }
}
