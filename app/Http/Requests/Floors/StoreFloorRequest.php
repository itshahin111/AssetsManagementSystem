<?php

namespace App\Http\Requests\Floors;

class StoreFloorRequest extends FloorRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->floorRules();
    }
}
