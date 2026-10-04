<?php

namespace Tests\Feature\Authorization;

use App\Models\Lietotajs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_guest_cannot_access_admin_users_list(): void
    {
        $this->getJson('/api/admin/users')->assertStatus(401);
    }

    
    public function test_regular_user_cannot_access_admin_users_list(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/admin/users')
            ->assertStatus(403);
    }

    
    public function test_admin_can_access_admin_users_list(): void
    {
        $admin = $this->createUser('admins');

        $this->withHeaders($this->authorizationHeader($admin))
            ->getJson('/api/admin/users')
            ->assertStatus(200);
    }

   
    public function test_guest_cannot_add_book_to_personal_library(): void
    {
        $this->postJson('/api/user/books/add', [])->assertStatus(401);
    }

    
    public function test_invalid_token_format_is_rejected(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid_token_no_underscore'])
            ->postJson('/api/user/books/add', [])
            ->assertStatus(401);
    }

    
    public function test_nonexistent_user_token_is_rejected(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer 99999_1234567890'])
            ->postJson('/api/user/books/add', [])
            ->assertStatus(401);
    }

    
    public function test_blocked_user_cannot_access_profile(): void
    {
        $user = $this->createUser('registretajsklients', 'blokets');

        $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/profile')
            ->assertStatus(403);
    }

    private function createUser(string $role = 'registretajsklients', string $status = 'aktivs'): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'TestReader',
            'epasts' => 'reader@example.com',
            'parole' => Hash::make('password123'),
            'loma' => $role,
            'registresanas_datums' => now()->toDateString(),
            'status' => $status,
        ]);
    }

    private function authorizationHeader(Lietotajs $user): array
    {
        $token = $user->kodsID . '_' . time();

        return ['Authorization' => 'Bearer ' . $token];
    }
}