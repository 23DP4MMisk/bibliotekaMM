<?php

namespace Tests\Feature\Profile;

use App\Models\Lietotajs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_profile(): void
    {
        $this->getJson('/api/profile')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/profile')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kodsID', $user->kodsID)
            ->assertJsonPath('data.epasts', $user->epasts);
    }

    public function test_authenticated_user_can_update_profile_fields(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->authorizationHeader($user))
            ->putJson('/api/profile', [
                'lietotaja_vards' => 'JaunaisVards',
                'bio' => 'Jauna biogrāfija',
                'pilseta' => 'Rīga',
                'dzim_datums' => '2001-05-15',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.lietotaja_vards', 'JaunaisVards')
            ->assertJsonPath('data.bio', 'Jauna biogrāfija');

        $this->assertDatabaseHas('Lietotajs', [
            'kodsID' => $user->kodsID,
            'lietotaja_vards' => 'JaunaisVards',
            'pilseta' => 'Rīga',
        ]);
    }

    public function test_profile_update_rejects_invalid_date(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->authorizationHeader($user))
            ->putJson('/api/profile', [
                'dzim_datums' => '2099-01-01',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_user_can_change_password_with_current_password(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->authorizationHeader($user))
            ->postJson('/api/profile/change-password', [
                'current_password' => 'password123',
                'new_password' => 'newPassword456',
                'new_password_confirmation' => 'newPassword456',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Parole veiksmīgi mainīta!');

        $this->assertTrue(Hash::check('newPassword456', $user->fresh()->parole));
    }

    public function test_user_can_delete_their_account_with_password(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->authorizationHeader($user))
            ->deleteJson('/api/profile', [
                'password' => 'password123',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Profils veiksmīgi dzēsts');

        $this->assertDatabaseMissing('Lietotajs', [
            'kodsID' => $user->kodsID,
        ]);
    }

    private function createUser(string $role = 'registretajsklients', string $status = 'aktivs'): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'ProfileUser',
            'epasts' => 'profile' . uniqid() . '@example.com',
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
