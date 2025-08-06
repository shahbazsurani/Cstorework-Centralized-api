<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Assign the LocationAdmin role and attach location.
     */
    public function assignLocationAdmin(User $user, \App\Models\Location $location)
    {
        if (!auth()->user()->hasRole('SuperAdmin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Assign LocationAdmin role
        $user->assignRole('LocationAdmin');

        // Attach location if not already attached
        $user->locations()->syncWithoutDetaching([$location->id]);

        return response()->json([
            'message' => 'LocationAdmin role assigned and location linked',
            'user' => $user->only(['name', 'email']),
            'location' => $location->only(['name', 'hash']),
        ]);
    }
}
