<?php

namespace Tests\Feature\Auth;

use App\Models\Lietotajs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/pieslēgties', [
            'epasts' => $user->epasts,
            'parole' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'token',
                'lietotajs' => ['kodsID', 'lietotaja_vards', 'epasts', 'loma', 'status'],
            ]);
    }

    
    public function test_login_token_matches_expected_format(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/pieslēgties', [
            'epasts' => $user->epasts,
            'parole' => 'password123',
        ]);

        $response->assertOk();
        $this->assertMatchesRegularExpression('/^\d+_\d+$/', $response->json('token'));
    }

    
    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/pieslēgties', [
            'epasts' => $user->epasts,
            'parole' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Nepareizs e-pasts vai parole');
    }

   
    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/pieslēgties', [
            'epasts' => 'missing@example.com',
            'parole' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    
    public function test_blocked_user_cannot_log_in(): void
    {
        $user = $this->createUser('blokets');

        $response = $this->postJson('/api/pieslēgties', [
            'epasts' => $user->epasts,
            'parole' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    private function createUser(string $status = 'aktivs'): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'BookReader',
            'epasts' => 'reader@example.com',
            'parole' => Hash::make('password123'),
            'loma' => 'registretajsklients',
            'registresanas_datums' => now()->toDateString(),
            'status' => $status,
        ]);
    }
}