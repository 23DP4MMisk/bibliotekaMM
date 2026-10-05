<?php

namespace Tests\Feature\Auth;

use App\Models\Lietotajs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Šis tests pārbauda, ka lietotājs var veiksmīgi ienākt sistēmā ar pareizu e-pastu un paroli.
     * $user = $this->createUser(); - izveido reālu lietotāju ar validām datiem.
     * $this->postJson('/api/pieslēgties', [...]) - nosūta pieprasījumu login endpointam.
     * Tiek gaidīts 200 un atgrieztas dati ar tokenu un lietotāja informāciju.
     */
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

    /**
     * Šis tests pārbauda, ka ģenerētais JWT/token formāts atbilst paredzētajam stilam ID_laiks.
     * $response = $this->postJson(...); - saņem login atbildi ar tokenu.
     * $response->json('token') - iegūst tokenu un pārbauda, vai tas atbilst regex piemēram 123_1234567890.
     */
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

    /**
     * Šis tests pārbauda, ka nepareiza parole tiek noraidīta.
     * $user = $this->createUser(); - izveido lietotāju ar pareizu paroli.
     * $this->postJson('/api/pieslēgties', ['parole' => 'wrong-password']) - mēģina pieslēgties ar nepareizu paroli.
     * Tiek gaidīts 401 un ziņa "Nepareizs e-pasts vai parole".
     */
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

    /**
     * Šis tests pārbauda, ka neesošs e-pasts tiek noraidīts.
     * $this->postJson('/api/pieslēgties', ['epasts' => 'missing@example.com']) - mēģina pieslēgties ar neesošu lietotāju.
     * Tiek gaidīts 401, kas nozīmē, ka lietotājs netika atrasts sistēmā.
     */
    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson('/api/pieslēgties', [
            'epasts' => 'missing@example.com',
            'parole' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Šis tests pārbauda, ka bloķēts lietotājs nevar pieslēgties sistēmai.
     * $user = $this->createUser('blokets'); - izveido lietotāju ar statusu 'blokets'.
     * $this->postJson('/api/pieslēgties', [...]) - mēģina ienākt ar bloķētu kontu.
     * Tiek gaidīts 403, jo konts ir bloķēts un piekļuve ir liegta.
     */
    public function test_blocked_user_cannot_log_in(): void
    {
        $user = $this->createUser('blokets');

        $response = $this->postJson('/api/pieslēgties', [
            'epasts' => $user->epasts,
            'parole' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Izveido lietotāju login testiem.
     * Metode ģenerē jaunu lietotāju ar noklusējuma statusu 'aktivs', bet ļauj norādīt arī 'blokets'.
     */
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