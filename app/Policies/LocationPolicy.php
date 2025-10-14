<?php

namespace App\Policies;

use App\Enums\Permission as Perm;
use App\Enums\Role;
use App\Models\Location;
use App\Models\User;

class LocationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Perm::LocationsView->value);
    }

    /**
     * Determine whether the user can view the model.
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
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Perm::LocationsCreate->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Location $location): bool
    {
        if ($user->hasRole(Role::SuperAdmin->value)) {
            return $user->can(Perm::LocationsUpdate->value);
        }

        return $user->can(Perm::LocationsUpdate->value)
            && $user->locations->contains('id', $location->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Location $location): bool
    {
        if ($user->hasRole(Role::SuperAdmin->value)) {
            return $user->can(Perm::LocationsDelete->value);
        }

        return $user->can(Perm::LocationsDelete->value)
            && $user->locations->contains('id', $location->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Location $location): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Location $location): bool
    {
        return false;
    }
}
