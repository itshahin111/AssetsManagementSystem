<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'asset_category_id' => $this->asset_category_id,
            'name'            => $this->name,
            'code'            => $this->code,
            'tracking_type'   => $this->tracking_type instanceof \App\Enums\AssetTrackingType
                ? $this->tracking_type->value
                : $this->tracking_type,
            'description'     => $this->description,
            'status'          => $this->status,
            'asset_category'  => new AssetCategoryResource($this->whenLoaded('category')),
            'assets_count'    => $this->whenCounted('assets'),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
