<?php

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Room */
class RoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'building_id' => $this->building_id,
            'floor_id' => $this->floor_id,
            'room_type_id' => $this->room_type_id,
            'name' => $this->name,
            'room_number' => $this->room_number,
            'code' => $this->code,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'building' => BuildingResource::make($this->whenLoaded('building')),
            'floor' => FloorResource::make($this->whenLoaded('floor')),
            'room_type' => RoomTypeResource::make($this->whenLoaded('roomType')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
