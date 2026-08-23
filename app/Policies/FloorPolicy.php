<?php

namespace App\Policies;

use App\Models\Floor;
use App\Models\User;
use App\Policies\Concerns\ChecksLocationPermission;

class FloorPolicy
{
    use ChecksLocationPermission;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'floors.view');
    }

    public function view(User $user, Floor $floor): bool
    {
        return $this->hasPermission($user, 'floors.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'floors.create');
    }

    public function update(User $user, Floor $floor): bool
    {
        return $this->hasPermission($user, 'floors.update');
    }

    public function delete(User $user, Floor $floor): bool
    {
        return $this->hasPermission($user, 'floors.delete');
    }
}
