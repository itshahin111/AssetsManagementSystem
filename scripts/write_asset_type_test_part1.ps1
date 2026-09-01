$path = 'C:\Users\Shahin\Desktop\Laravel\Inventory\tests\Feature\Api\V1\AssetTypeApiTest.php'
$content = @'
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

'@
[System.IO.File]::WriteAllText($path, $content)