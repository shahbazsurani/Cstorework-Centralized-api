<?php

namespace Tests\Support;

use App\Models\User;
use App\Models\Location;
use App\Enums\Role as RoleEnum;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

trait ActsAs
{
    protected function seedRoles(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure permissions exist
        foreach (\App\Enums\Permission::cases() as $perm) {
            SpatiePermission::findOrCreate($perm->value);
        }

        // Ensure roles exist
        $roles = [];
        foreach (RoleEnum::cases() as $case) {
            $roles[$case->value] = Role::findOrCreate($case->value);
        }

        // Assign permissions
        // SuperAdmin gets all permissions
        $roles[RoleEnum::SuperAdmin->value]->givePermissionTo(collect(\App\Enums\Permission::cases())->map->value->all());

        // LocationAdmin gets full Locations permissions
        $roles[RoleEnum::LocationAdmin->value]->givePermissionTo([
            \App\Enums\Permission::LocationsView->value,
            \App\Enums\Permission::LocationsCreate->value,
            \App\Enums\Permission::LocationsUpdate->value,
            \App\Enums\Permission::LocationsDelete->value,
        ]);

        // UserAdmin can view locations for user management UI purposes
        if (isset($roles[RoleEnum::UserAdmin->value])) {
            $roles[RoleEnum::UserAdmin->value]->givePermissionTo([
                \App\Enums\Permission::LocationsView->value,
            ]);
        }
    }

    protected function loginAsRole(string $role, array $locationIds = [], array $appIds = []): User
    {
        $this->seedRoles();

        /** @var User $user */
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->assignRole($role);

        if (!empty($locationIds)) {
            $user->locations()->sync($locationIds);
        }

        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    protected function authHeaders(User $user): array
    {
        // Using Sanctum actingAs, we typically don't need manual headers in HTTP tests.
        // However, for explicit Bearer tests, we'll generate a token.
        $token = $user->createToken('test')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }
}
