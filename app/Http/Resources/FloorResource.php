<?php

namespace App\Http\Resources;

use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Floor */
class FloorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'name' => $this->name,
            'level' => $this->level,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'rooms_count' => $this->whenCounted('rooms'),
            'building' => BuildingResource::make($this->whenLoaded('building')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
