<?php

namespace Tests\Feature\UserLibrary;

use App\Models\Gramata;
use App\Models\Lietotajs;
use App\Models\Nodala;
use App\Models\Zanrs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserLibraryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Šis tests pārbauda, ka viesis nevar skatīt personālo bibliotēku.
     * $this->getJson('/api/user/books') - mēģina pieprasīt bibliotēkas datus bez tokena.
     * Tiek gaidīts 401, jo lietotājs nav autentificēts.
     */
    public function test_guest_cannot_view_personal_library(): void
    {
        $this->getJson('/api/user/books')
            ->assertStatus(401);
    }

    /**
     * Šis tests pārbauda, ka autentificēts lietotājs redz tikai savu bibliotēku.
     * $user = $this->createUser(); - izveido lietotāju.
     * DB::table('LietotajGramatas')->insert([...]) - pievieno grāmatu konkrētam lietotājam.
     * $this->withHeaders(...)->getJson('/api/user/books') - pieprasa lietotāja bibliotēku.
     * Tiek gaidīts 200 un success = true.
     */
    public function test_authenticated_user_can_view_their_own_library(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();

        DB::table('LietotajGramatas')->insert([
            'Lietotajs' => $user->kodsID,
            'Gramatas' => $book->ISBN,
            'statuss' => 'lasu',
            'pievienosanas_datums' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withHeaders($this->headersFor($user))
            ->getJson('/api/user/books')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    /**
     * Šis tests pārbauda, ka autentificēts lietotājs var pievienot grāmatu savai bibliotēkai.
     * $user = $this->createUser(); - izveido lietotāju.
     * $book = $this->createBook(); - izveido grāmatu, ko pievienot bibliotēkai.
     * $this->withHeaders(...)->postJson('/api/user/books/add', ['isbn' => $book->ISBN, 'statuss' => 'lasu']) - nosūta pieprasījumu.
     * Tiek gaidīts 200 un ieraksts jāsaglabā lietotāja bibliotēkas tabulā.
     */
    public function test_authenticated_user_can_add_book_to_library(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();

        $this->withHeaders($this->headersFor($user))
            ->postJson('/api/user/books/add', [
                'isbn' => $book->ISBN,
                'statuss' => 'lasu',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('LietotajGramatas', [
            'Lietotajs' => $user->kodsID,
            'Gramatas' => $book->ISBN,
            'statuss' => 'lasu',
        ]);
    }

    /**
     * Šis tests pārbauda, ka to pašu grāmatu nevar pievienot divreiz vienai personālajai bibliotēkai.
     * Vispirms tiek pievienota grāmata ar statusu 'lasu'.
     * Pēc tam tiek mēģināts pievienot to pašu grāmatu vēlreiz ar citu statusu.
     * Tiek gaidīts 400, jo dublikāts ir aizliegts.
     */
    public function test_duplicate_book_in_library_is_rejected(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();

        $this->withHeaders($this->headersFor($user))
            ->postJson('/api/user/books/add', [
                'isbn' => $book->ISBN,
                'statuss' => 'lasu',
            ])
            ->assertStatus(200);

        $this->withHeaders($this->headersFor($user))
            ->postJson('/api/user/books/add', [
                'isbn' => $book->ISBN,
                'statuss' => 'izlasiju',
            ])
            ->assertStatus(400);
    }

    private function createUser(string $role = 'registretajsklients', string $status = 'aktivs'): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'LibraryReader',
            'epasts' => 'reader' . uniqid() . '@example.com',
            'parole' => Hash::make('password123'),
            'loma' => $role,
            'registresanas_datums' => now()->toDateString(),
            'status' => $status,
        ]);
    }

    private function createBook(): Gramata
    {
        $nodala = Nodala::create(['tips' => 'akademiska']);
        $zanrs = Zanrs::create([
            'nosaukums' => 'Personal library genre ' . uniqid(),
            'Nodala' => $nodala->Nodala_ID,
        ]);

        return Gramata::create([
            'ISBN' => (string) (1000000000 + random_int(1, 999999999)),
            'nosaukums' => 'Library book ' . uniqid(),
            'autors' => 'Jānis Rainis',
            'Zanra_ID' => $zanrs->Zanra_ID,
            'Nodala_ID' => $nodala->Nodala_ID,
        ]);
    }

    private function tokenFor(Lietotajs $user): string
    {
        return $user->kodsID . '_' . time();
    }

    private function headersFor(Lietotajs $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenFor($user)];
    }
}
