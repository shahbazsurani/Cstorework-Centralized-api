<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use App\Enums\Role;
use App\Enums\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure permissions exist
        $allPermissions = [
            // Users
            Permission::UsersView->value,
            Permission::UsersCreate->value,
            Permission::UsersUpdate->value,
            Permission::UsersDelete->value,
            // Locations
            Permission::LocationsView->value,
            Permission::LocationsCreate->value,
            Permission::LocationsUpdate->value,
            Permission::LocationsDelete->value,
            // Applications
            Permission::ApplicationsView->value,
            Permission::ApplicationsCreate->value,
            Permission::ApplicationsUpdate->value,
            Permission::ApplicationsDelete->value,
        ];

        foreach ($allPermissions as $perm) {
            SpatiePermission::findOrCreate($perm);
        }

        // Ensure roles exist
        $roles = [
            Role::SuperAdmin->value,
            Role::ApplicationAdmin->value,
            Role::LocationAdmin->value,
            Role::UserAdmin->value,
            Role::ReadOnly->value,
            Role::ReadWrite->value,
        ];

        foreach ($roles as $roleName) {
            SpatieRole::findOrCreate($roleName);
        }

        // Map permissions to roles
        $rolePermissions = [
            Role::SuperAdmin->value => $allPermissions, // all permissions
            Role::ApplicationAdmin->value => [
                Permission::ApplicationsView->value,
                Permission::ApplicationsCreate->value,
                Permission::ApplicationsUpdate->value,
                Permission::ApplicationsDelete->value,
            ],
            Role::LocationAdmin->value => [
                Permission::LocationsView->value,
                Permission::LocationsCreate->value,
                Permission::LocationsUpdate->value,
                Permission::LocationsDelete->value,
            ],
            Role::UserAdmin->value => [
                Permission::UsersView->value,
                Permission::UsersCreate->value,
                Permission::UsersUpdate->value,
                Permission::UsersDelete->value,
            ],
            Role::ReadOnly->value => [
                Permission::UsersView->value,
                Permission::LocationsView->value,
                Permission::ApplicationsView->value,
            ],
            Role::ReadWrite->value => [
                Permission::UsersView->value,
                Permission::UsersCreate->value,
                Permission::UsersUpdate->value,
                Permission::LocationsView->value,
                Permission::LocationsCreate->value,
                Permission::LocationsUpdate->value,
                Permission::ApplicationsView->value,
                Permission::ApplicationsCreate->value,
                Permission::ApplicationsUpdate->value,
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = SpatieRole::findByName($roleName);
            $role->syncPermissions($perms);
        }
    }
}
