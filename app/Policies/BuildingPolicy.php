<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;
use App\Policies\Concerns\ChecksLocationPermission;

class BuildingPolicy
{
    use ChecksLocationPermission;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'buildings.view');
    }

    public function view(User $user, Building $building): bool
    {
        return $this->hasPermission($user, 'buildings.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'buildings.create');
    }

    public function update(User $user, Building $building): bool
    {
        return $this->hasPermission($user, 'buildings.update');
    }

    public function delete(User $user, Building $building): bool
    {
        return $this->hasPermission($user, 'buildings.delete');
    }
}
