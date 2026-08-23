<?php

namespace App\Http\Requests\RoomTypes;

use App\Models\RoomType;

class UpdateRoomTypeRequest extends RoomTypeRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var RoomType $roomType */
        $roomType = $this->route('room_type');

        return $this->roomTypeRules($roomType);
    }
}
