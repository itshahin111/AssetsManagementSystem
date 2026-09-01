<?php

namespace App\Http\Requests\AssetCategories;

use App\Http\Requests\ApiFormRequest;
use App\Models\AssetCategory;
use Illuminate\Validation\Rule;

abstract class AssetCategoryRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function assetCategoryRules(?AssetCategory $category): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('asset_categories', 'name')
                    ->whereNull('deleted_at')
                    ->ignore($category?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('asset_categories', 'code')
                    ->whereNull('deleted_at')
                    ->ignore($category?->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'status'      => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
