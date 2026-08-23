<?php

namespace App\Http\Requests\Buildings;

class StoreBuildingRequest extends BuildingRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->buildingRules();
    }
}
