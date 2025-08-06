<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['SuperAdmin', 'LocationAdmin', 'ApplicationAdmin', 'UserAdmin', 'User'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }
}
