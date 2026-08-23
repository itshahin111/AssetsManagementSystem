<?php

namespace App\Http\Requests\Floors;

use App\Http\Requests\ApiFormRequest;
use App\Models\Floor;
use Illuminate\Validation\Rule;

abstract class FloorRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function floorRules(?Floor $floor = null): array
    {
        return [
            'building_id' => [
                'required',
                'integer',
                Rule::exists('buildings', 'id')->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('floors', 'name')
                    ->where(fn ($query) => $query->where('building_id', $this->integer('building_id')))
                    ->ignore($floor),
            ],
            'level' => ['required', 'integer', 'min:-32768', 'max:32767'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
