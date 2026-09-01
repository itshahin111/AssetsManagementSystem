<?php

namespace Tests\Feature\Api\V1;

use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\User;
use Database\Seeders\AssetTaxonomyPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetTypeApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AssetTaxonomyPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo([
            'asset_types.view',
            'asset_types.create',
            'asset_types.update',
            'asset_types.delete',
            'asset_categories.view',
        ]);
    }

    public function test_an_authorized_user_can_list_asset_types(): void
    {
        $category = AssetCategory::factory()->create();
        AssetType::factory()->create([
            'asset_category_id' => $category->id,
            'name' => 'Desktop Computer',
            'code' => 'COMP-DSK',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/asset-types')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Desktop Computer');
    }
}
