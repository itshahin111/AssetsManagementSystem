<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LocationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
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
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $all = Permission::query()->whereIn('name', $permissions)->get();
        $viewOnly = $all->filter(fn (Permission $permission) => str_ends_with($permission->name, '.view'));
        $roomPermissions = $all->filter(fn (Permission $permission) => str_starts_with($permission->name, 'rooms.'));

        Role::findOrCreate('Super Admin', 'web')->syncPermissions($all);
        Role::findOrCreate('Admin', 'web')->syncPermissions($all);
        Role::findOrCreate('Inventory Manager', 'web')->syncPermissions(
            $viewOnly->merge($roomPermissions)->unique('id')->values(),
        );
        Role::findOrCreate('IT Manager', 'web')->syncPermissions($viewOnly);
        Role::findOrCreate('Staff', 'web')->syncPermissions([]);
        Role::findOrCreate('Viewer', 'web')->syncPermissions($viewOnly);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
