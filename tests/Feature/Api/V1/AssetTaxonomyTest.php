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

    public function test_an_authorized_user_can_create_and_list_asset_categories(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-categories', [
                'name'        => 'Test Equipment',
                'code'        => 'TEST',
                'description' => 'A test category',
                'status'      => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.code', 'TEST')
            ->assertJsonPath('data.name', 'Test Equipment');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/asset-categories')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Test Equipment');
    }

    public function test_an_authorized_user_can_update_an_asset_category(): void
    {
        $category = AssetCategory::create([
            'name'   => 'Old Name',
            'code'   => 'OLD',
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/asset-categories/{$category->id}", [
                'name'        => 'New Name',
                'code'        => 'OLD',
                'description' => 'Updated',
                'status'      => 'inactive',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('asset_categories', [
            'id'     => $category->id,
            'name'   => 'New Name',
            'status' => 'inactive',
        ]);
    }

    public function test_an_asset_category_with_active_types_cannot_be_deleted(): void
    {
        $category = AssetCategory::create([
            'name'   => 'With Types',
            'code'   => 'WT',
            'status' => 'active',
        ]);

        AssetType::create([
            'asset_category_id' => $category->id,
            'name'              => 'A Type',
            'code'              => 'WT-1',
            'tracking_type'     => 'individual',
            'status'            => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/asset-categories/{$category->id}")
            ->assertConflict()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('asset_categories', [
            'id'         => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_an_asset_category_with_no_types_can_be_deleted(): void
    {
        $category = AssetCategory::create([
            'name'   => 'Empty',
            'code'   => 'EMPTY',
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/asset-categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('asset_categories', ['id' => $category->id]);
    }

    public function test_an_authorized_user_can_create_and_list_asset_types(): void
    {
        $category = AssetCategory::create([
            'name'   => 'Computer',
            'code'   => 'COMP',
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-types', [
                'asset_category_id' => $category->id,
                'name'              => 'Laptop',
                'code'              => 'COMP-LAP',
                'tracking_type'     => 'individual',
                'status'            => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Laptop')
            ->assertJsonPath('data.tracking_type', 'individual')
            ->assertJsonPath('data.asset_category.code', 'COMP');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/asset-types')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Laptop');
    }

    public function test_an_asset_type_can_be_updated(): void
    {
        $category = AssetCategory::create([
            'name'   => 'Category A',
            'code'   => 'CA',
            'status' => 'active',
        ]);

        $type = AssetType::create([
            'asset_category_id' => $category->id,
            'name'              => 'Old Type',
            'code'              => 'OT',
            'tracking_type'     => 'individual',
            'status'            => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/asset-types/{$type->id}", [
                'asset_category_id' => $category->id,
                'name'              => 'New Type',
                'code'              => 'OT',
                'tracking_type'     => 'quantity',
                'status'            => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Type')
            ->assertJsonPath('data.tracking_type', 'quantity');

        $this->assertDatabaseHas('asset_types', [
            'id'            => $type->id,
            'name'          => 'New Type',
            'tracking_type' => 'quantity',
        ]);
    }

    public function test_an_asset_type_can_be_deleted(): void
    {
        $category = AssetCategory::create([
            'name'   => 'C',
            'code'   => 'C',
            'status' => 'active',
        ]);

        $type = AssetType::create([
            'asset_category_id' => $category->id,
            'name'              => 'T',
            'code'              => 'T-1',
            'tracking_type'     => 'individual',
            'status'            => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/asset-types/{$type->id}")
            ->assertOk();

        $this->assertSoftDeleted('asset_types', ['id' => $type->id]);
    }

    public function test_a_unique_name_constraint_is_enforced_per_category(): void
    {
        $category = AssetCategory::create([
            'name'   => 'C',
            'code'   => 'C',
            'status' => 'active',
        ]);

        AssetType::create([
            'asset_category_id' => $category->id,
            'name'              => 'Desktop',
            'code'              => 'DESK-1',
            'tracking_type'     => 'individual',
            'status'            => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-types', [
                'asset_category_id' => $category->id,
                'name'              => 'Desktop',
                'code'              => 'DESK-2',
                'tracking_type'     => 'individual',
                'status'            => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_a_code_must_be_unique_globally(): void
    {
        $cat1 = AssetCategory::create([
            'name'   => 'A',
            'code'   => 'A',
            'status' => 'active',
        ]);
        $cat2 = AssetCategory::create([
            'name'   => 'B',
            'code'   => 'B',
            'status' => 'active',
        ]);

        AssetType::create([
            'asset_category_id' => $cat1->id,
            'name'              => 'Item 1',
            'code'              => 'SAME',
            'tracking_type'     => 'individual',
            'status'            => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-types', [
                'asset_category_id' => $cat2->id,
                'name'              => 'Item 2',
                'code'              => 'SAME',
                'tracking_type'     => 'individual',
                'status'            => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    public function test_tracking_type_is_required(): void
    {
        $category = AssetCategory::create([
            'name'   => 'C',
            'code'   => 'C',
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-types', [
                'asset_category_id' => $category->id,
                'name'              => 'Type',
                'code'              => 'T-1',
                'status'            => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tracking_type']);
    }

    public function test_tracking_type_rejects_invalid_values(): void
    {
        $category = AssetCategory::create([
            'name'   => 'C',
            'code'   => 'C',
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-types', [
                'asset_category_id' => $category->id,
                'name'              => 'Quantity Item',
                'code'              => 'QI-1',
                'tracking_type'     => 'random',
                'status'            => 'active',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tracking_type']);
    }

    public function test_tracking_type_stores_valid_enum_values(): void
    {
        $category = AssetCategory::create([
            'name'   => 'C',
            'code'   => 'C',
            'status' => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/asset-types', [
                'asset_category_id' => $category->id,
                'name'              => 'Quantity Item',
                'code'              => 'QI-1',
                'tracking_type'     => 'quantity',
                'status'            => 'active',
            ])
            ->assertCreated();

        $type = AssetType::query()->latest('id')->first();
        $this->assertSame('quantity', $type->tracking_type->value);
        $this->assertSame(
            \App\Enums\AssetTrackingType::Quantity,
            $type->tracking_type
        );
    }

    public function test_users_without_taxonomy_permissions_receive_forbidden_response(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/asset-categories')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are not authorized to perform this action.');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/asset-types')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'You are not authorized to perform this action.');
    }

    public function test_asset_types_can_be_filtered_by_category(): void
    {
        $catA = AssetCategory::create([
            'name'   => 'A',
            'code'   => 'A',
            'status' => 'active',
        ]);
        $catB = AssetCategory::create([
            'name'   => 'B',
            'code'   => 'B',
            'status' => 'active',
        ]);

        AssetType::create([
            'asset_category_id' => $catA->id,
            'name'              => 'Item A',
            'code'              => 'A-1',
            'tracking_type'     => 'individual',
            'status'            => 'active',
        ]);

        AssetType::create([
            'asset_category_id' => $catB->id,
            'name'              => 'Item B',
            'code'              => 'B-1',
            'tracking_type'     => 'quantity',
            'status'            => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/asset-types?asset_category_id={$catA->id}")
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Item A');
    }

    public function test_asset_types_can_be_filtered_by_tracking_type(): void
    {
        $cat = AssetCategory::create([
            'name'   => 'C',
            'code'   => 'C',
            'status' => 'active',
        ]);

        AssetType::create([
            'asset_category_id' => $cat->id,
            'name'              => 'I1',
            'code'              => 'I-1',
            'tracking_type'     => 'individual',
            'status'            => 'active',
        ]);

        AssetType::create([
            'asset_category_id' => $cat->id,
            'name'              => 'Q1',
            'code'              => 'Q-1',
            'tracking_type'     => 'quantity',
            'status'            => 'active',
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/asset-types?tracking_type=quantity')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Q1');
    }
}
