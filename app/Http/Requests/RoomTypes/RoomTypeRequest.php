<?php

namespace App\Http\Requests\RoomTypes;

use App\Http\Requests\ApiFormRequest;
use App\Models\RoomType;
use Illuminate\Validation\Rule;

abstract class RoomTypeRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function roomTypeRules(?RoomType $roomType = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('room_types', 'name')->ignore($roomType),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
