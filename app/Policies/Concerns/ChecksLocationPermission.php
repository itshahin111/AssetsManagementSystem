<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksLocationPermission
{
    private function hasPermission(User $user, string $permission): bool
    {
        return $user->can($permission);
    }
}
