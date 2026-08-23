<?php

namespace App\Http\Requests\Buildings;

use App\Http\Requests\ApiFormRequest;
use App\Models\Building;
use Illuminate\Validation\Rule;

abstract class BuildingRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function buildingRules(?Building $building = null): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('buildings', 'code')->ignore($building),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }
}
