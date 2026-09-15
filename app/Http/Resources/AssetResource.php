<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'asset_category_id' => $this->asset_category_id,
            'asset_type_id'   => $this->asset_type_id,
            'building_id'     => $this->building_id,
            'floor_id'        => $this->floor_id,
            'room_id'         => $this->room_id,
            'name'            => $this->name,
            'code'            => $this->code,
            'status'          => $this->status,
            'sort_order'      => $this->sort_order,
            'description'     => $this->description,
            'asset_category'  => new AssetCategoryResource($this->whenLoaded('assetCategory')),
            'asset_type'      => new AssetTypeResource($this->whenLoaded('assetType')),
            'building'        => new BuildingResource($this->whenLoaded('building')),
            'floor'           => new FloorResource($this->whenLoaded('floor')),
            'room'            => new RoomResource($this->whenLoaded('room')),
            'creator'         => $this->whenLoaded('creator'),
            'updater'         => $this->whenLoaded('updater'),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
            'deleted_at'      => $this->deleted_at?->toIso8601String(),
        ];
    }
}
