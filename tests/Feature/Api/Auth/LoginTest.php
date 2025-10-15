<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success_returns_token_and_user(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $res = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'secret123',
        ]);

        $res->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['name','email','roles']
            ]);
    }

    public function test_login_wrong_password_returns_401(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('correct'),
        ]);

        $res = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong',
        ]);

        $res->assertStatus(401);
    }
}
