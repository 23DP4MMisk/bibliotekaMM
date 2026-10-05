<?php

namespace Tests\Feature\Authorization;

use App\Models\Lietotajs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Šis tests pārbauda, ka viesis nevar piekļūt administrācijas lietotāju sarakstam.
     * $this->getJson('/api/admin/users') - izsauc admin endpointu bez autentifikācijas.
     * Tiek gaidīts 401, jo piekļuve ir aizliegta bez tokena.
     */
    public function test_guest_cannot_access_admin_users_list(): void
    {
        $this->getJson('/api/admin/users')->assertStatus(401);
    }

    /**
     * Šis tests pārbauda, ka parasts lietotājs nevar piekļūt administratora sadaļai.
     * $user = $this->createUser(); - izveido parastu lietotāju.
     * $this->withHeaders($this->authorizationHeader($user))->getJson('/api/admin/users') - nosūta pieprasījumu ar lietotāja tokenu.
     * Tiek gaidīts 403, jo lietotājam nav admin tiesību.
     */
    public function test_regular_user_cannot_access_admin_users_list(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/admin/users')
            ->assertStatus(403);
    }

    /**
     * Šis tests pārbauda, ka administrators var atvērt lietotāju sarakstu.
     * $admin = $this->createUser('admins'); - izveido lietotāju ar admin lomu.
     * $this->withHeaders($this->authorizationHeader($admin))->getJson('/api/admin/users') - pieprasa admin datu skatu.
     * Tiek gaidīts 200, jo adminam ir tiesības skatīt šo sadaļu.
     */
    public function test_admin_can_access_admin_users_list(): void
    {
        $admin = $this->createUser('admins');

        $this->withHeaders($this->authorizationHeader($admin))
            ->getJson('/api/admin/users')
            ->assertStatus(200);
    }

    /**
     * Šis tests pārbauda, ka viesis nevar pievienot grāmatu savam personālajam plauktam.
     * $this->postJson('/api/user/books/add', []) - mēģina nosūtīt pieprasījumu bez autentifikācijas.
     * Tiek gaidīts 401, jo personālā bibliotēka ir aizsargāta.
     */
    public function test_guest_cannot_add_book_to_personal_library(): void
    {
        $this->postJson('/api/user/books/add', [])->assertStatus(401);
    }

    /**
     * Šis tests pārbauda, ka nederīgs token formāts tiek noraidīts.
     * $this->withHeaders(['Authorization' => 'Bearer invalid_token_no_underscore']) - nosūta nepareizu tokenu bez underscore rakstzīmes.
     * Tiek gaidīts 401, jo API nepieņem nederīgu autorizācijas formātu.
     */
    public function test_invalid_token_format_is_rejected(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer invalid_token_no_underscore'])
            ->postJson('/api/user/books/add', [])
            ->assertStatus(401);
    }

    /**
     * Šis tests pārbauda, ka tokens, kurš atbilst formātam, bet pieder neesošam lietotājam, tiek noraidīts.
     * $this->withHeaders(['Authorization' => 'Bearer 99999_1234567890']) - nosūta tokenu ar neesošu lietotāja ID.
     * Tiek gaidīts 401, jo lietotāja nav datubāzē.
     */
    public function test_nonexistent_user_token_is_rejected(): void
    {
        $this->withHeaders(['Authorization' => 'Bearer 99999_1234567890'])
            ->postJson('/api/user/books/add', [])
            ->assertStatus(401);
    }

    /**
     * Šis tests pārbauda, ka bloķēts lietotājs nevar piekļūt savam profilam.
     * $user = $this->createUser('registretajsklients', 'blokets'); - izveido lietotāju ar statusu 'blokets'.
     * $this->withHeaders($this->authorizationHeader($user))->getJson('/api/profile') - mēģina atvērt profila endpointu.
     * Tiek gaidīts 403, jo bloķēts lietotājs nedrīkst piekļūt profilam.
     */
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