<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class LocationHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $classroom = RoomType::query()->firstOrCreate(
            ['name' => 'Classroom'],
            ['description' => 'Standard teaching room', 'status' => 'active'],
        );

        RoomType::query()->firstOrCreate(
            ['name' => 'Laboratory'],
            ['description' => 'Practical teaching laboratory', 'status' => 'active'],
        );

        RoomType::query()->firstOrCreate(
            ['name' => 'Office'],
            ['description' => 'Administrative office', 'status' => 'active'],
        );

        $building = Building::query()->firstOrCreate(
            ['code' => 'MC'],
            [
                'name' => 'Main Campus',
                'description' => 'Primary school building',
                'status' => 'active',
                'sort_order' => 1,
            ],
        );

        $groundFloor = Floor::query()->firstOrCreate(
            ['building_id' => $building->id, 'name' => 'Ground Floor'],
            ['level' => 0, 'sort_order' => 1, 'status' => 'active'],
        );

        $firstFloor = Floor::query()->firstOrCreate(
            ['building_id' => $building->id, 'name' => 'First Floor'],
            ['level' => 1, 'sort_order' => 2, 'status' => 'active'],
        );

        Room::query()->firstOrCreate(
            ['code' => 'MC-G-101'],
            [
                'building_id' => $building->id,
                'floor_id' => $groundFloor->id,
                'room_type_id' => $classroom->id,
                'name' => 'Classroom 101',
                'room_number' => '101',
                'capacity' => 30,
                'status' => 'active',
            ],
        );

        Room::query()->firstOrCreate(
            ['code' => 'MC-L1-201'],
            [
                'building_id' => $building->id,
                'floor_id' => $firstFloor->id,
                'room_type_id' => $classroom->id,
                'name' => 'Classroom 201',
                'room_number' => '201',
                'capacity' => 30,
                'status' => 'active',
            ],
        );
    }
}
