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

    public function test_guest_cannot_view_personal_library(): void
    {
        $this->getJson('/api/user/books')
            ->assertStatus(401);
    }

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

    // An authenticated user can add a book to their personal library.
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

    // The same book cannot be added twice to the same personal library.
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
