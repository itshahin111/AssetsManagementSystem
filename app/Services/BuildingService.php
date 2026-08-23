<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\Building;

class BuildingService
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Building
    {
        return Building::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Building $building, array $attributes): Building
    {
        $building->update($attributes);

        return $building->refresh();
    }

    public function delete(Building $building): void
    {
        if ($building->floors()->exists() || $building->rooms()->exists()) {
            throw new ConflictException('A building with active floors or rooms cannot be deleted.');
        }

        $building->delete();
    }
}
