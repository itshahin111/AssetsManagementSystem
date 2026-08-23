<?php

namespace App\Http\Requests\Rooms;

class StoreRoomRequest extends RoomRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->roomRules();
    }
}
