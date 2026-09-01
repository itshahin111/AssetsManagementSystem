$path = 'C:\Users\Shahin\Desktop\Laravel\Inventory\tests\Feature\Api\V1\AssetTaxonomyTest.php'
$content = @'
<?php

namespace Tests\Feature\Api\V1;

use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\User;
use Database\Seeders\AssetTaxonomyPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AssetTaxonomyPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'asset_categories.view',
            'asset_categories.create',
            'asset_categories.update',
            'asset_categories.delete',
            'asset_types.view',
            'asset_types.create',
            'asset_types.update',
            'asset_types.delete',
        ]);
    }

    public function test_an_authorized_user_can_list_asset_categories(): void
    {
        AssetCategory::factory()->create(['name' => 'Computer Equipment', 'code' => 'COMP']);
        AssetCategory::factory()->create(['name' => 'Furniture', 'code' => 'FURN']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/asset-categories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.name', 'Computer Equipment');
    }

    public function test_an_authorized_user_can_create_an_asset_category(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-categories', [
                'name' => 'Computer Equipment',
                'code' => 'COMP',
                'description' => 'Desktops, laptops, and related IT hardware.',
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'COMP')
            ->assertJsonPath('data.name', 'Computer Equipment');

        $this->assertDatabaseHas('asset_categories', [
            'code' => 'COMP',
            'name' => 'Computer Equipment',
        ]);
    }

    public function test_asset_category_code_must_be_unique(): void
    {
        AssetCategory::factory()->create(['code' => 'COMP']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-categories', [
                'name' => 'Different Name',
                'code' => 'COMP',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_asset_category_name_must_be_unique(): void
    {
        AssetCategory::factory()->create(['name' => 'Computer Equipment']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-categories', [
                'name' => 'Computer Equipment',
                'code' => 'DIFF',
                'status' => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_an_authorized_user_can_update_an_asset_category(): void
    {
        $category = AssetCategory::factory()->create(['name' => 'Old Name', 'code' => 'OLD']);

        $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/asset-categories/{$category->id}", [
                'name' => 'New Name',
                'code' => 'NEW',
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.code', 'NEW');
    }

    public function test_a_category_with_asset_types_cannot_be_deleted(): void
    {
        $category = AssetCategory::factory()->create();
        AssetType::factory()->create(['asset_category_id' => $category->id]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/asset-categories/{$category->id}")
            ->assertConflict()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'A category assigned to active asset types cannot be deleted.');

        $this->assertDatabaseHas('asset_categories', ['id' => $category->id, 'deleted_at' => null]);
    }

    public function test_an_authorized_user_can_delete_an_empty_asset_category(): void
    {
        $category = AssetCategory::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/asset-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('asset_categories', ['id' => $category->id]);
    }
}
'@
[System.IO.File]::WriteAllText($path, $content)