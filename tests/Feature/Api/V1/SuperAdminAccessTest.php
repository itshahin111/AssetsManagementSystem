<?php

namespace Tests\Feature\Api\V1;

use App\Models\AssetCategory;
use App\Models\Building;
use App\Models\User;
use Database\Seeders\AssetTaxonomyPermissionSeeder;
use Database\Seeders\LocationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LocationPermissionSeeder::class);
        $this->seed(AssetTaxonomyPermissionSeeder::class);
    }

    public function test_super_admin_can_access_any_endpoint_without_specific_permission(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/buildings')
            ->assertOk();

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/floors')
            ->assertOk();

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/rooms')
            ->assertOk();

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/room-types')
            ->assertOk();

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/asset-categories')
            ->assertOk();

        $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/asset-types')
            ->assertOk();
    }

    public function test_super_admin_can_create_resources_without_explicit_permission(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/asset-categories', [
                'name'   => 'Super Admin Category',
                'code'   => 'SA-CAT',
                'status' => 'active',
            ])
            ->assertCreated();

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/buildings', [
                'name'   => 'Super Admin Building',
                'code'   => 'SA-BLD',
                'status' => 'active',
            ])
            ->assertCreated();
    }

    public function test_non_super_admin_without_permission_is_forbidden(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser, 'sanctum')
            ->getJson('/api/v1/asset-categories')
            ->assertForbidden();
    }

    public function test_gate_before_returns_true_for_super_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->assertTrue($superAdmin->can('any.permission.string'));
        $this->assertTrue($superAdmin->can('buildings.create'));
        $this->assertTrue($superAdmin->can('asset_categories.delete'));
    }

    public function test_gate_before_returns_null_for_non_super_admin(): void
    {
        $regularUser = User::factory()->create();

        $this->assertFalse($regularUser->can('buildings.create'));
    }

    public function test_super_admin_can_update_and_delete_resources(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $category = AssetCategory::create([
            'name'   => 'Test Category',
            'code'   => 'TEST',
            'status' => 'active',
        ]);

        $this->actingAs($superAdmin, 'sanctum')
            ->putJson("/api/v1/asset-categories/{$category->id}", [
                'name'   => 'Updated Category',
                'code'   => 'TEST',
                'status' => 'active',
            ])
            ->assertOk();

        $this->actingAs($superAdmin, 'sanctum')
            ->deleteJson("/api/v1/asset-categories/{$category->id}")
            ->assertOk();
    }

    public function test_super_admin_me_response_includes_super_admin_role(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $response = $this->actingAs($superAdmin, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertOk();

        $response->assertJsonPath('data.roles.0', 'Super Admin');
    }

    public function test_building_creation_by_super_admin(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('Super Admin');

        $this->actingAs($superAdmin, 'sanctum')
            ->postJson('/api/v1/buildings', [
                'name'   => 'New Building',
                'code'   => 'NB-001',
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'New Building');
    }
}