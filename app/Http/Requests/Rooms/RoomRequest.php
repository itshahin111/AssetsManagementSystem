<?php

namespace App\Http\Requests\Rooms;

use App\Http\Requests\ApiFormRequest;
use App\Models\Room;
use Illuminate\Validation\Rule;

abstract class RoomRequest extends ApiFormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function roomRules(?Room $room = null): array
    {
        return [
            'building_id' => ['required', 'integer', Rule::exists('buildings', 'id')->whereNull('deleted_at')],
            'floor_id' => ['required', 'integer', Rule::exists('floors', 'id')->whereNull('deleted_at')],
            'room_type_id' => ['nullable', 'integer', Rule::exists('room_types', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:100'],
            'room_number' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('rooms', 'room_number')
                    ->where(fn ($query) => $query->where('floor_id', $this->integer('floor_id')))
                    ->ignore($room),
            ],
            'code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('rooms', 'code')->ignore($room),
            ],
            'capacity' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
