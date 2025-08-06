<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $locationAdmin = Role::firstOrCreate(['name' => 'LocationAdmin']);
        $applicationAdmin = Role::firstOrCreate(['name' => 'ApplicationAdmin']);
        $userAdmin = Role::firstOrCreate(['name' => 'UserAdmin']);
        $user = Role::firstOrCreate(['name' => 'User']);

    }
}
