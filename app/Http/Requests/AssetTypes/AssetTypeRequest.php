<?php

namespace App\Http\Requests\AssetTypes;

use App\Http\Requests\ApiFormRequest;
use App\Models\AssetType;
use Illuminate\Validation\Rule;

abstract class AssetTypeRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function assetTypeRules(?AssetType $type): array
    {
        return [
            'asset_category_id' => [
                'required',
                'integer',
                Rule::exists('asset_categories', 'id')->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('asset_types', 'name')
                    ->where('asset_category_id', $this->input('asset_category_id'))
                    ->whereNull('deleted_at')
                    ->ignore($type?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('asset_types', 'code')
                    ->whereNull('deleted_at')
                    ->ignore($type?->id),
            ],
            'tracking_type' => ['required', Rule::in(['individual', 'quantity'])],
            'description'   => ['nullable', 'string', 'max:2000'],
            'status'        => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
