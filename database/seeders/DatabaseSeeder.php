<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(RoleSeeder::class);

        $user = User::create([
            'name' => 'Shahbaz Surani',
            'email' => 'shahbazsurani@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('Test123@'),
            'remember_token' => Str::random(10),
        ]);

        $user->assignRole('SuperAdmin');
    }
}
