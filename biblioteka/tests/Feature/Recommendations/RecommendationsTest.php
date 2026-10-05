<?php

namespace Tests\Feature\Recommendations;

use App\Models\Gramata;
use App\Models\Lietotajs;
use App\Models\Nodala;
use App\Models\Zanrs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RecommendationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_popular_recommendations(): void
    {
        $this->createBook('Dienas grāmata');
        $this->createBook('Nakts grāmata');

        $this->getJson('/api/recommendations')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['isbn', 'nosaukums', 'autors', 'score'],
                ],
            ]);
    }

    public function test_authenticated_user_without_library_gets_popular_recommendations(): void
    {
        $user = $this->createUser();
        $this->createBook('Pirma grāmata');
        $this->createBook('Otrā grāmata');

        $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/recommendations')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['isbn', 'nosaukums', 'autors'],
                ],
            ]);
    }

    public function test_authenticated_user_with_library_gets_personalized_recommendations(): void
    {
        $user = $this->createUser();

        $nodala = Nodala::create(['tips' => 'akademiska']);
        $zanrs = Zanrs::create([
            'nosaukums' => 'Recommended genre ' . uniqid(),
            'Nodala' => $nodala->Nodala_ID,
        ]);

        $bookInLibrary = Gramata::create([
            'ISBN' => (string) (1000000000 + random_int(1, 99999999)),
            'nosaukums' => 'Mājas grāmata ' . uniqid(),
            'autors' => 'Autors A',
            'Zanra_ID' => $zanrs->Zanra_ID,
            'Nodala_ID' => $nodala->Nodala_ID,
        ]);

        $recommended = Gramata::create([
            'ISBN' => (string) (2000000000 + random_int(1, 99999999)),
            'nosaukums' => 'Ieteiktā grāmata ' . uniqid(),
            'autors' => 'Autors B',
            'Zanra_ID' => $zanrs->Zanra_ID,
            'Nodala_ID' => $nodala->Nodala_ID,
        ]);

        DB::table('LietotajGramatas')->insert([
            'Lietotajs' => $user->kodsID,
            'Gramatas' => $bookInLibrary->ISBN,
            'statuss' => 'lasu',
            'pievienosanas_datums' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/recommendations');

        $response
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['nosaukums' => $recommended->nosaukums])
            ->assertJsonMissing(['nosaukums' => $bookInLibrary->nosaukums]);
    }

    public function test_registered_user_with_empty_library_falls_back_to_popular_recommendations(): void
    {
        $user = $this->createUser();
        $popularTitle = 'Populārākā grāmata ' . uniqid();
        $this->createBook($popularTitle);

        $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/recommendations')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['nosaukums' => $popularTitle]);
    }

    public function test_registered_user_prefers_books_from_their_most_common_nodala(): void
    {
        $user = $this->createUser();

        $preferredNodala = Nodala::create(['tips' => 'akademiska']);
        $otherNodala = Nodala::create(['tips' => 'izglitojosa']);

        $preferredGenre = Zanrs::create([
            'nosaukums' => 'Preferred genre ' . uniqid(),
            'Nodala' => $preferredNodala->Nodala_ID,
        ]);
        $otherGenre = Zanrs::create([
            'nosaukums' => 'Other genre ' . uniqid(),
            'Nodala' => $otherNodala->Nodala_ID,
        ]);

        $ownedPreferredBook = Gramata::create([
            'ISBN' => (string) (4000000000 + random_int(1, 99999999)),
            'nosaukums' => 'Owned preferred ' . uniqid(),
            'autors' => 'Author A',
            'Zanra_ID' => $preferredGenre->Zanra_ID,
            'Nodala_ID' => $preferredNodala->Nodala_ID,
        ]);

        $ownedPreferredBookTwo = Gramata::create([
            'ISBN' => (string) (5000000000 + random_int(1, 99999999)),
            'nosaukums' => 'Owned preferred 2 ' . uniqid(),
            'autors' => 'Author B',
            'Zanra_ID' => $preferredGenre->Zanra_ID,
            'Nodala_ID' => $preferredNodala->Nodala_ID,
        ]);

        $ownedOtherBook = Gramata::create([
            'ISBN' => (string) (6000000000 + random_int(1, 99999999)),
            'nosaukums' => 'Owned other ' . uniqid(),
            'autors' => 'Author C',
            'Zanra_ID' => $otherGenre->Zanra_ID,
            'Nodala_ID' => $otherNodala->Nodala_ID,
        ]);

        $recommendedTitle = 'Recommended preferred ' . uniqid();
        $recommendedPreferredBook = Gramata::create([
            'ISBN' => (string) (7000000000 + random_int(1, 99999999)),
            'nosaukums' => $recommendedTitle,
            'autors' => 'Author D',
            'Zanra_ID' => $preferredGenre->Zanra_ID,
            'Nodala_ID' => $preferredNodala->Nodala_ID,
        ]);

        foreach ([$ownedPreferredBook, $ownedPreferredBookTwo, $ownedOtherBook] as $book) {
            DB::table('LietotajGramatas')->insert([
                'Lietotajs' => $user->kodsID,
                'Gramatas' => $book->ISBN,
                'statuss' => 'lasu',
                'pievienosanas_datums' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/recommendations')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['nosaukums' => $recommendedTitle])
            ->assertJsonMissing(['nosaukums' => $ownedPreferredBook->nosaukums])
            ->assertJsonMissing(['nosaukums' => $ownedOtherBook->nosaukums]);
    }

    private function createUser(string $role = 'registretajsklients', string $status = 'aktivs'): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'RecommendedUser',
            'epasts' => 'recommend' . uniqid() . '@example.com',
            'parole' => Hash::make('password123'),
            'loma' => $role,
            'registresanas_datums' => now()->toDateString(),
            'status' => $status,
        ]);
    }

    private function createBook(string $title): Gramata
    {
        $nodala = Nodala::create(['tips' => 'izglitojosa']);
        $zanrs = Zanrs::create([
            'nosaukums' => 'Rec genre ' . uniqid(),
            'Nodala' => $nodala->Nodala_ID,
        ]);

        return Gramata::create([
            'ISBN' => (string) (3000000000 + random_int(1, 99999999)),
            'nosaukums' => $title,
            'autors' => 'Autors C',
            'Zanra_ID' => $zanrs->Zanra_ID,
            'Nodala_ID' => $nodala->Nodala_ID,
        ]);
    }

    private function authorizationHeader(Lietotajs $user): array
    {
        $token = $user->kodsID . '_' . time();

        return ['Authorization' => 'Bearer ' . $token];
    }
}
