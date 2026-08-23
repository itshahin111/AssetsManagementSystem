<?php

namespace App\Http\Requests\Buildings;

use App\Models\Building;

class UpdateBuildingRequest extends BuildingRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Building $building */
        $building = $this->route('building');

        return $this->buildingRules($building);
    }
}
