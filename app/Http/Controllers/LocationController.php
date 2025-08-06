<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

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

        if ($user->hasRole('LocationAdmin')) {
            return $user->locations; // only assigned locations
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate(['name' => 'required|string']);

        $location = Location::create($data);

        if ($user->hasRole('LocationAdmin')) {
            $user->locations()->attach($location->id);
        }

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
