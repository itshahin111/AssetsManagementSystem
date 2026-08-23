<?php

namespace App\Http\Requests\RoomTypes;

class StoreRoomTypeRequest extends RoomTypeRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->roomTypeRules();
    }
}
