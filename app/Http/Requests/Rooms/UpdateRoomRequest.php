<?php

namespace App\Http\Requests\Rooms;

use App\Models\Room;

class UpdateRoomRequest extends RoomRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Room $room */
        $room = $this->route('room');

        return $this->roomRules($room);
    }
}
