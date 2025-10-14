<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Enums\Role as RoleEnum;
use App\Enums\Permission as PermEnum;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_and_permissions_are_seeded(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        // Assert roles exist
        foreach (RoleEnum::cases() as $role) {
            $this->assertNotNull(
                SpatieRole::where('name', $role->value)->first(),
                "Role {$role->value} was not seeded"
            );
        }

        // Assert permissions exist
        foreach (PermEnum::cases() as $perm) {
            $this->assertNotNull(
                SpatiePermission::where('name', $perm->value)->first(),
                "Permission {$perm->value} was not seeded"
            );
        }
    }
}
