<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(RoleSeeder::class);

        User::factory()->create([
            'name' => 'Shahbaz Surani',
            'email' => 'shahbazsurani@gmail.com',
            'password' => 'Test123@',
            'role_id' => '1',
        ]);
    }
}
