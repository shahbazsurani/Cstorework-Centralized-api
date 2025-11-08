<?php

namespace App\Policies;

use App\Enums\Permission as Perm;
use App\Enums\Role;
use App\Models\Location;
use App\Models\User;

class LocationSettingsPolicy
{
    /**
     * Determine whether the user can view location settings.
     */
    public function view(User $user, Location $location): bool
    {
        // SuperAdmin can view all
        if ($user->hasRole(Role::SuperAdmin->value)) {
            return true;
        }

        // Other users need permission and to be assigned to the location
        return $user->can(Perm::LocationsView->value)
            && $user->locations->contains('id', $location->id);
    }

    /**
     * Determine whether the user can create/store location settings.
     */
    public function create(User $user, Location $location): bool
    {
        // SuperAdmin can create for all locations
        if ($user->hasRole(Role::SuperAdmin->value)) {
            return $user->can(Perm::LocationsUpdate->value);
        }

        // Other users need update permission and to be assigned to the location
        return $user->can(Perm::LocationsUpdate->value)
            && $user->locations->contains('id', $location->id);
    }

    /**
     * Determine whether the user can update location settings.
     */
    public function update(User $user, Location $location): bool
    {
        // SuperAdmin can update all
        if ($user->hasRole(Role::SuperAdmin->value)) {
            return $user->can(Perm::LocationsUpdate->value);
        }

        // Other users need update permission and to be assigned to the location
        return $user->can(Perm::LocationsUpdate->value)
            && $user->locations->contains('id', $location->id);
    }

    /**
     * Determine whether the user can delete location settings.
     */
    public function delete(User $user, Location $location): bool
    {
        // SuperAdmin can delete all
        if ($user->hasRole(Role::SuperAdmin->value)) {
            return $user->can(Perm::LocationsDelete->value);
        }

        // Other users need delete permission and to be assigned to the location
        return $user->can(Perm::LocationsDelete->value)
            && $user->locations->contains('id', $location->id);
    }
}
