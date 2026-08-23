<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\Floor;

class FloorService
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Floor
    {
        return Floor::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Floor $floor, array $attributes): Floor
    {
        $floor->update($attributes);

        return $floor->refresh();
    }

    public function delete(Floor $floor): void
    {
        if ($floor->rooms()->exists()) {
            throw new ConflictException('A floor with active rooms cannot be deleted.');
        }

        $floor->delete();
    }
}
