<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $creator = $request->user(); // authenticated user

        $newRole = $request->input('role');
        $locationIds = $request->input('location_ids', []);

        // Validation (optional)
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|exists:roles,name',
        ]);

        // Check creator's permission to assign role
        if ($creator->hasRole('SuperAdmin')) {
            // allowed
        } elseif ($creator->hasRole('LocationAdmin')) {
            $allowedRoles = ['LocationAdmin', 'ApplicationAdmin', 'UserAdmin', 'User'];

            if (!in_array($newRole, $allowedRoles)) {
                return response()->json(['error' => 'Forbidden role assignment'], 403);
            }

            // Ensure they don’t assign locations they don’t control
            $creatorLocationIds = $creator->locations()->pluck('id')->toArray();

            if (array_diff($locationIds, $creatorLocationIds)) {
                return response()->json(['error' => 'You can only assign locations you control'], 403);
            }
        } else {
            return response()->json(['error' => 'You are not allowed to create users'], 403);
        }

        // Create new user
        $newUser = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        // Assign role
        $newUser->assignRole($newRole);

        // Attach locations if any
        if (!empty($locationIds)) {
            $newUser->locations()->sync($locationIds);
        }

        return response()->json(['message' => 'User created', 'user' => $newUser], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();


        return response()->json([
            'token' => $user->createToken('api-token')->plainTextToken,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }
}
