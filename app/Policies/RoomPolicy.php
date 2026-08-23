<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Policies\Concerns\ChecksLocationPermission;

class RoomPolicy
{
    use ChecksLocationPermission;

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'rooms.view');
    }

    public function view(User $user, Room $room): bool
    {
        return $this->hasPermission($user, 'rooms.view');
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'rooms.create');
    }

    public function update(User $user, Room $room): bool
    {
        return $this->hasPermission($user, 'rooms.update');
    }

    public function delete(User $user, Room $room): bool
    {
        return $this->hasPermission($user, 'rooms.delete');
    }
}
