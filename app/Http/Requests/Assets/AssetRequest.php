<?php

namespace App\Http\Requests\Assets;

use App\Http\Requests\ApiFormRequest;
use App\Models\Asset;
use Illuminate\Validation\Rule;

abstract class AssetRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function assetRules(?Asset $asset = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('assets', 'code')
                    ->whereNull('deleted_at')
                    ->ignore($asset?->id),
            ],
            'asset_category_id' => [
                'required',
                'integer',
                Rule::exists('asset_categories', 'id')->whereNull('deleted_at'),
            ],
            'asset_type_id' => [
                'required',
                'integer',
                Rule::exists('asset_types', 'id')->whereNull('deleted_at'),
            ],
            'building_id' => [
                'required',
                'integer',
                Rule::exists('buildings', 'id')->whereNull('deleted_at'),
            ],
            'floor_id' => [
                'required',
                'integer',
                Rule::exists('floors', 'id')->whereNull('deleted_at'),
            ],
            'room_id' => [
                'required',
                'integer',
                Rule::exists('rooms', 'id')->whereNull('deleted_at'),
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:65535',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
