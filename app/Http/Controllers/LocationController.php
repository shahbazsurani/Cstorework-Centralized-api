<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\User;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('SuperAdmin')) {
            return Location::all();
        }

        return $user->locations;
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('SuperAdmin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $location = Location::create(['name' => $request->name]);

        return response()->json($location, 201);
    }

    public function show(Request $request, Location $location)
    {
        $user = $request->user();

        if ($user->hasRole('SuperAdmin')) return $location;

        if ($user->hasRole('LocationAdmin') && $user->locations->contains($location)) {
            return $location;
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    public function update(Request $request, Location $location)
    {
        $user = $request->user();

        if (
            $user->hasRole('SuperAdmin') ||
            ($user->hasRole('LocationAdmin') && $user->locations->contains($location))
        ) {
            $location->update($request->only('name'));
            return response()->json($location);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    public function destroy(Request $request, Location $location)
    {
        $user = $request->user();

        if (
            $user->hasRole('SuperAdmin') ||
            ($user->hasRole('LocationAdmin') && $user->locations->contains($location))
        ) {
            $location->delete();
            return response()->json(['message' => 'Deleted']);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

}
