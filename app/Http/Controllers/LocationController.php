<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Http\Requests\LocationStoreRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Http\Resources\LocationResource;
use App\Enums\Role;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Location::class);

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $search = trim((string) $request->query('q', ''));

        $user = $request->user();

        if ($user->hasRole(Role::SuperAdmin->value)) {
            $query = Location::query();
        } else {
            $query = $user->locations()->getQuery();
        }

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $locations = $query->orderBy('name')->paginate($perPage)->appends($request->query());

        return LocationResource::collection($locations);
    }

    public function store(LocationStoreRequest $request)
    {
        $this->authorize('create', Location::class);

        $location = Location::create($request->validated());

        return (new LocationResource($location))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Location $location)
    {
        $this->authorize('view', $location);

        return new LocationResource($location);
    }

    public function update(LocationUpdateRequest $request, Location $location)
    {
        $this->authorize('update', $location);

        $location->update($request->validated());

        return new LocationResource($location);
    }

    public function destroy(Request $request, Location $location)
    {
        $this->authorize('delete', $location);

        $location->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
