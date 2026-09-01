$root = $PSScriptRoot
$path = Join-Path $root 'tests/Feature/Api/V1/AssetTaxonomyTest.php'
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
'@
Set-Content -Path $path -Value $content -NoNewline -Encoding UTF8
Write-Output "Wrote header"
