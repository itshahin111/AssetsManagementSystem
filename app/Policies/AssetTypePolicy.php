<?php

namespace App\Policies;

use App\Models\AssetType;
use App\Models\User;
use App\Policies\Concerns\ChecksLocationPermission;

class AssetTypePolicy
{
    use ChecksLocationPermission;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'asset_types.view');
    }

    public function view(User $user, AssetType $type): bool
    {
        return $this->hasPermission($user, 'asset_types.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'asset_types.create');
    }

    public function update(User $user, AssetType $type): bool
    {
        return $this->hasPermission($user, 'asset_types.update');
    }

    public function delete(User $user, AssetType $type): bool
    {
        return $this->hasPermission($user, 'asset_types.delete');
    }
}
