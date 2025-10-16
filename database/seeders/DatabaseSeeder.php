<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Enums\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // Seed default Applications (base URLs per env)
        $this->call(ApplicationSeeder::class);

        // Optionally create an initial SuperAdmin if env values are set
        $adminEmail = env('SEED_ADMIN_EMAIL');
        if ($adminEmail) {
            $adminName = env('SEED_ADMIN_NAME', 'Super Admin');
            $adminPassword = env('SEED_ADMIN_PASSWORD');

            if (! $adminPassword) {
                // Generate a temporary password if none provided
                $adminPassword = Str::random(16);
            }

            $user = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'email_verified_at' => now(),
                    'password' => bcrypt($adminPassword),
                    'remember_token' => Str::random(10),
                ]
            );

            if (! $user->hasRole(Role::SuperAdmin->value)) {
                $user->assignRole(Role::SuperAdmin->value);
            }
        }
    }
}
