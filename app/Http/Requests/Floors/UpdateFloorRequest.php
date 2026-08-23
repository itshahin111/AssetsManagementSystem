<?php

namespace App\Http\Requests\Floors;

use App\Models\Floor;

class UpdateFloorRequest extends FloorRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Floor $floor */
        $floor = $this->route('floor');

        return $this->floorRules($floor);
    }
}
