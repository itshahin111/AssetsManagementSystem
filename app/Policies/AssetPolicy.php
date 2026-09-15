<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use App\Policies\Concerns\ChecksLocationPermission;

class AssetPolicy
{
    use ChecksLocationPermission;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'assets.view');
    }

    public function view(User $user, Asset $asset): bool
    {
        return $this->hasPermission($user, 'assets.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'assets.create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $this->hasPermission($user, 'assets.update');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $this->hasPermission($user, 'assets.delete');
    }
}
