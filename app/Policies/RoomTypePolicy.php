<?php

namespace App\Policies;

use App\Models\RoomType;
use App\Models\User;
use App\Policies\Concerns\ChecksLocationPermission;

class RoomTypePolicy
{
    use ChecksLocationPermission;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'room_types.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'room_types.create');
    }

    public function update(User $user, RoomType $roomType): bool
    {
        return $this->hasPermission($user, 'room_types.update');
    }

    public function delete(User $user, RoomType $roomType): bool
    {
        return $this->hasPermission($user, 'room_types.delete');
    }
}
