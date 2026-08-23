<?php

namespace Tests\Feature\Api\V1;

use App\Models\Building;
use App\Models\Floor;
use App\Models\RoomType;
use App\Models\User;
use Database\Seeders\LocationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LocationPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'buildings.view',
            'buildings.create',
            'buildings.update',
            'buildings.delete',
            'floors.view',
            'floors.create',
            'floors.update',
            'floors.delete',
            'rooms.view',
            'rooms.create',
            'rooms.update',
            'rooms.delete',
        ]);
    }

    public function test_an_authorized_user_can_create_and_list_buildings(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/buildings', [
                'name' => 'Main Campus',
                'code' => 'MC',
                'description' => 'The principal campus',
                'status' => 'active',
                'sort_order' => 1,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'MC');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/buildings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Main Campus');
    }

    public function test_a_room_must_belong_to_its_selected_buildings_floor(): void
    {
        $mainCampus = Building::query()->create([
            'name' => 'Main Campus',
            'code' => 'MC',
            'status' => 'active',
        ]);
        $annex = Building::query()->create([
            'name' => 'Annex',
            'code' => 'ANX',
            'status' => 'active',
        ]);
        $floor = Floor::query()->create([
            'building_id' => $mainCampus->id,
            'name' => 'Ground Floor',
            'level' => 0,
            'status' => 'active',
        ]);
        $roomType = RoomType::query()->create([
            'name' => 'Classroom',
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/rooms', [
                'building_id' => $annex->id,
                'floor_id' => $floor->id,
                'room_type_id' => $roomType->id,
                'name' => 'Classroom 101',
                'room_number' => '101',
                'code' => 'ANX-G-101',
                'capacity' => 30,
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.floor_id.0', 'The selected floor does not belong to the selected building.');

        $this->assertDatabaseCount('rooms', 0);
    }

    public function test_a_building_with_active_children_cannot_be_deleted(): void
    {
        $building = Building::query()->create([
            'name' => 'Main Campus',
            'code' => 'MC',
            'status' => 'active',
        ]);

        Floor::query()->create([
            'building_id' => $building->id,
            'name' => 'Ground Floor',
            'level' => 0,
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/buildings/{$building->id}")
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'A building with active floors or rooms cannot be deleted.');

        $this->assertDatabaseHas('buildings', ['id' => $building->id, 'deleted_at' => null]);
    }

    public function test_users_without_a_required_permission_receive_a_consistent_forbidden_response(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/buildings')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are not authorized to perform this action.');
    }

    public function test_login_returns_a_sanctum_token_and_the_authorized_user(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
            'device_name' => 'feature-test',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', $this->user->email)
            ->assertJsonStructure(['data' => ['token']]);
    }
}
