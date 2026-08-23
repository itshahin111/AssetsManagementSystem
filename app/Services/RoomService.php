<?php

namespace App\Services;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Validation\ValidationException;

class RoomService
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Room
    {
        $this->ensureFloorBelongsToBuilding(
            buildingId: (int) $attributes['building_id'],
            floorId: (int) $attributes['floor_id'],
        );

        return Room::query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Room $room, array $attributes): Room
    {
        $this->ensureFloorBelongsToBuilding(
            buildingId: (int) $attributes['building_id'],
            floorId: (int) $attributes['floor_id'],
        );

        $room->update($attributes);

        return $room->refresh();
    }

    public function delete(Room $room): void
    {
        $room->delete();
    }

    private function ensureFloorBelongsToBuilding(int $buildingId, int $floorId): void
    {
        $floor = Floor::query()->findOrFail($floorId);

        if ($floor->building_id !== $buildingId) {
            throw ValidationException::withMessages([
                'floor_id' => ['The selected floor does not belong to the selected building.'],
            ]);
        }
    }
}
