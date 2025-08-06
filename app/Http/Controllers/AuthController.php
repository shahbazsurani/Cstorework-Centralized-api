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
        // Get role: First user is SuperAdmin, rest are User
        if(User::count() === 0) {
            $validated = $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed',
            ]);

            $role = Role::where('name', 'SuperAdmin')->first();

            // Create user with role
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $role->id,
            ]);

            return response()->json([
                'token' => $user->createToken('api-token')->plainTextToken,
                'user' => $user,
            ]);
        }

        $user = $request->user();

        // Check if user is SuperAdmin
        if (!$user || $user->role->name !== 'SuperAdmin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed',
            'role' => 'required|in:User,UserAdmin,LocationAdmin,ApplicationAdmin', // restrict roles
        ]);

        $role = Role::where('name', $validated['role'])->first();

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
        ]);

        return response()->json([
            'user' => $newUser,
            'token' => $newUser->createToken('api-token')->plainTextToken,
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user()->load('role');

        return response()->json([
            'token' => $user->createToken('api-token')->plainTextToken,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? null,
            ],
        ]);
    }
}
