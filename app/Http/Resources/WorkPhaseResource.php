<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkPhaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->RECORD_ID,
            'flass' => $this->FLASS,
            'idopr' => $this->IDOPR,
            'flseq' => $this->FLSEQ,
            'fllav' => $this->FLLAV,
            'fldes' => $this->FLDES,
            'flqta' => (float) $this->FLQTA,
            'flqtb' => (float) $this->FLQTB,
            'flqtd' => (float) $this->FLQTD,
            'flcon' => $this->FLCON,
            'dtnum' => $this->DTNUM,
            'tempo' => (int) $this->TEMPO,
            'dtras' => $this->DTRAS,
            'drdes' => $this->DRDES,
            'dtric' => $this->DTRIC,
            'is_assigned' => $this->when(
                $this->relationLoaded('assignments'),
                fn() => $this->assignments->isNotEmpty()
            ),
            'assignment' => $this->whenLoaded('assignments', function () {
                return $this->assignments->isNotEmpty() 
                    ? new WorkPhaseAssignmentResource($this->assignments->first())
                    : null;
            })
        ];
    }
}
