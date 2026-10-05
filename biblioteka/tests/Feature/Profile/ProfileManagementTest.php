<?php

namespace Tests\Feature\Profile;

use App\Models\Lietotajs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Šis tests pārbauda, ka viesis nevar skatīt profila datus.
     * $this->getJson('/api/profile') - mēģina pieprasīt profila endpointu bez Bearer tokena.
     * Tiek gaidīts 401 un success = false, jo lietotājs nav autentificēts.
     */
    public function test_guest_cannot_view_profile(): void
    {
        $this->getJson('/api/profile')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    /**
     * Šis tests pārbauda, ka autentificēts lietotājs var skatīt savu profila informāciju.
     * $user = $this->createUser(); - izveido lietotāju.
     * $this->withHeaders($this->authorizationHeader($user))->getJson('/api/profile') - nosūta autorizētu pieprasījumu.
     * Tiek gaidīts 200 un atgrieztie dati satur lietotāja kodsID un e-pastu.
     */
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

    /**
     * Šis tests pārbauda profila atjaunināšanu.
     * $user = $this->createUser(); - izveido lietotāju.
     * $this->withHeaders(...)->putJson('/api/profile', [...]) - nosūta datu atjauninājumu ar vārdu, bio, pilsētu un dzimšanas datumu.
     * Tiek gaidīts 200 un datubāzē jābūt saglabātiem jaunajiem profila laukiem.
     */
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

    /**
     * Šis tests pārbauda, ka nederīgs dzimšanas datums tiek noraidīts.
     * $this->withHeaders(...)->putJson('/api/profile', ['dzim_datums' => '2099-01-01']) - mēģina ielikt nākotnes datumu.
     * Tiek gaidīts 422, jo datums nav atļauts no validācijas viedokļa.
     */
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

    /**
     * Šis tests pārbauda paroles maiņu ar pašreizējo paroli.
     * $user = $this->createUser(); - izveido lietotāju.
     * $this->withHeaders(...)->postJson('/api/profile/change-password', [...]) - nosūta aktuālo un jauno paroli.
     * Tiek gaidīts 200 un jaunā parole jābūt saglabātai hash formā.
     */
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

    /**
     * Šis tests pārbauda profila dzēšanu ar paroli.
     * $user = $this->createUser(); - izveido lietotāju.
     * $this->withHeaders(...)->deleteJson('/api/profile', ['password' => 'password123']) - nosūta dzēšanas pieprasījumu ar pareizu paroli.
     * Tiek gaidīts 200 un datubāzē lietotājs jābūt noņemts.
     */
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
