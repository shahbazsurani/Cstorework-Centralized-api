<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Enums\Role;
use App\Http\Resources\UserResource;
use App\Http\Resources\LocationResource;

class UserController extends Controller
{
    /**
     * Assign the LocationAdmin role and attach location.
     */
    public function assignLocationAdmin(User $user, Location $location)
    {
        if (!auth()->user()->hasRole(Role::SuperAdmin->value)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Assign LocationAdmin role
        $user->assignRole(Role::LocationAdmin->value);

        // Attach location if not already attached
        $user->locations()->syncWithoutDetaching([$location->id]);

        return response()->json([
            'message' => 'LocationAdmin role assigned and location linked',
            'user' => UserResource::make($user),
            'location' => LocationResource::make($location),
        ]);
    }
}
