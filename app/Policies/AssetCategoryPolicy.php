<?php

namespace App\Policies;

use App\Models\AssetCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksLocationPermission;

class AssetCategoryPolicy
{
    use ChecksLocationPermission;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'asset_categories.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'asset_categories.create');
    }

    public function update(User $user, AssetCategory $category): bool
    {
        return $this->hasPermission($user, 'asset_categories.update');
    }

    public function delete(User $user, AssetCategory $category): bool
    {
        return $this->hasPermission($user, 'asset_categories.delete');
    }
}
