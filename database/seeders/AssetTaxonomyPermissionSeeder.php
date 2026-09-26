<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AssetTaxonomyPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'asset_categories.view',
            'asset_categories.create',
            'asset_categories.update',
            'asset_categories.delete',
            'asset_types.view',
            'asset_types.create',
            'asset_types.update',
            'asset_types.delete',
            'assets.view',
            'assets.create',
            'assets.update',
            'assets.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $all = Permission::query()->whereIn('name', $permissions)->get();

        Role::findOrCreate('Super Admin', 'web')->syncPermissions($all);
        Role::findOrCreate('Admin', 'web')->syncPermissions($all);
        Role::findOrCreate('Inventory Manager', 'web')->syncPermissions($all);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
