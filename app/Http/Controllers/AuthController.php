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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Rotate: revoke previous tokens to avoid sprawl
        try { $user->tokens()->delete(); } catch (\Throwable) {}

        $new = $user->createToken('api-token');
        // Set expiration (30 days by default); Sanctum will treat expired tokens as invalid
        if (method_exists($new, 'accessToken') || property_exists($new, 'accessToken')) {
            $tokenModel = $new->accessToken; // Laravel\Sanctum\PersonalAccessToken
            if ($tokenModel) {
                $ttl = (int) config('token-auth.token_ttl_minutes', 30);
                $tokenModel->forceFill(['expires_at' => now()->addMinutes($ttl)])->save();
            }
        }

        return response()->json([
            'token' => $new->plainTextToken,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }
}
