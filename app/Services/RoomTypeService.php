<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\RoomType;

class RoomTypeService
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): RoomType
    {
        return RoomType::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(RoomType $roomType, array $attributes): RoomType
    {
        $roomType->update($attributes);

        return $roomType->refresh();
    }

    public function delete(RoomType $roomType): void
    {
        if ($roomType->rooms()->exists()) {
            throw new ConflictException('A room type assigned to active rooms cannot be deleted.');
        }

        $roomType->delete();
    }
}
