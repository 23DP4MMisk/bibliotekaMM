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

    /**
     * Šis tests pārbauda, ka viesis saņem populārākās grāmatas ieteikumus.
     * $this->createBook('Dienas grāmata'); - izveido grāmatas, no kurām veidot ieteikumus.
     * $this->getJson('/api/recommendations') - izsauc ieteikumu endpointu bez autorizācijas.
     * Tiek gaidīts 200 un atbildē jābūt "success = true" un grāmatu datiem.
     */
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

    /**
     * Šis tests pārbauda, ka autentificēts lietotājs bez bibliotēkas saņem standarta populāros ieteikumus.
     * $user = $this->createUser(); - izveido reālu lietotāju.
     * $this->withHeaders(...)->getJson('/api/recommendations') - pieprasa ieteikumus ar Bearer tokenu.
     * Jo lietotājam nav grāmatu bibliotēkā, API jāatgriež populārie ieteikumi.
     */
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

    /**
     * Šis tests pārbauda personalizētos ieteikumus lietotājam, kuram ir bibliotēka.
     * $user = $this->createUser(); - izveido lietotāju.
     * $bookInLibrary = Gramata::create(...) - izveido grāmatu, kas jau atrodas lietotāja bibliotēkā.
     * $recommended = Gramata::create(...) - izveido grāmatu, kas jāieteiks kā ieteikums.
     * $this->withHeaders(...)->getJson('/api/recommendations') - lūdz ieteikumus.
     * Tiek gaidīts, ka atgrieztajā sarakstā būs ieteiktā grāmata, bet nevis jau esošā grāmata bibliotēkā.
     */
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
            ->assertJsonFragment(['nosaukums' => $bookInLibrary->nosaukums]);
    }

    /**
     * Šis tests pārbauda, ka reģistrētam lietotājam ar tukšu bibliotēku API atkāpjas uz populāriem ieteikumiem.
     * $user = $this->createUser(); - izveido lietotāju.
     * $popularTitle = 'Populārākā grāmata...' - pievieno zināmu grāmatu.
     * $this->withHeaders(...)->getJson('/api/recommendations') - pieprasa ieteikumus.
     * Tiek gaidīts, ka atgriezīs tādu populāro grāmatu kā rezultātu.
     */
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

    /**
     * Pārbauda, ka viesim un reģistrētam lietotājam ar tukšu bibliotēku grāmatas kārto tikai pēc skatījumu skaita.
     * Grāmatai ar vairāk skatījumiem jābūt pirmajai pat tad, ja citai grāmatai ir vairāk lejupielāžu.
     */
    public function test_guest_and_empty_library_recommendations_are_sorted_by_views_only(): void
    {
        $user = $this->createUser();
        $mostViewed = $this->createBook('Visvairāk skatītā grāmata');
        $mostDownloaded = $this->createBook('Visvairāk lejupielādētā grāmata');

        DB::table('Parskata')->insert([
            'parskatas_skaits' => 20,
            'Gramatas' => $mostViewed->ISBN,
            'Lietotajs' => $user->kodsID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('Parskata')->insert([
            'parskatas_skaits' => 2,
            'Gramatas' => $mostDownloaded->ISBN,
            'Lietotajs' => $user->kodsID,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($index = 0; $index < 10; $index++) {
            DB::table('Lejupielade')->insert([
                'Datums' => now()->toDateString(),
                'Gramatas_ID' => $mostDownloaded->ISBN,
                'Lietotaja_ID' => $user->kodsID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $guestResponse = $this->getJson('/api/recommendations')
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame($mostViewed->nosaukums, $guestResponse->json('data.0.nosaukums'));

        $emptyLibraryResponse = $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/recommendations')
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame($mostViewed->nosaukums, $emptyLibraryResponse->json('data.0.nosaukums'));
    }

    /**
     * Pārbauda, ka ieteikumu sadaļu proporcijas seko lietotāja bibliotēkai.
     * Pārbauda sadalījumus 4:2, 2:4 un 3:3, kā arī atkārtoti pieprasa ieteikumus pēc jaunu grāmatu pievienošanas bibliotēkai.
     */
    public function test_recommendation_section_distribution_matches_library_and_updates_after_addition(): void
    {
        $academic = Nodala::create(['tips' => 'akademiska']);
        $entertainment = Nodala::create(['tips' => 'izglitojosa']);
        $createBookForSection = function (string $title, Nodala $section, int $isbn): Gramata {
            $genre = Zanrs::create([
                'nosaukums' => 'Ratio genre ' . uniqid(),
                'Nodala' => $section->Nodala_ID,
            ]);

            return Gramata::create([
                'ISBN' => $isbn,
                'nosaukums' => $title,
                'autors' => 'Test Author',
                'Zanra_ID' => $genre->Zanra_ID,
                'Nodala_ID' => $section->Nodala_ID,
            ]);
        };

        $books = ['academic' => [], 'entertainment' => []];
        for ($index = 0; $index < 10; $index++) {
            $books['academic'][] = $createBookForSection('Academic ' . $index, $academic, 1100000000 + $index);
            $books['entertainment'][] = $createBookForSection('Entertainment ' . $index, $entertainment, 1200000000 + $index);
        }

        $scenarios = [
            ['academic' => 4, 'entertainment' => 2, 'expectedAcademic' => 4],
            ['academic' => 2, 'entertainment' => 4, 'expectedAcademic' => 2],
            ['academic' => 3, 'entertainment' => 3, 'expectedAcademic' => 3],
        ];

        foreach ($scenarios as $scenarioIndex => $scenario) {
            $user = $this->createUser();
            foreach (['academic' => $academic, 'entertainment' => $entertainment] as $key => $section) {
                foreach (array_slice($books[$key], 0, $scenario[$key]) as $book) {
                    DB::table('LietotajGramatas')->insert([
                        'Lietotajs' => $user->kodsID,
                        'Gramatas' => $book->ISBN,
                        'statuss' => 'lasu',
                        'pievienosanas_datums' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $response = $this->withHeaders($this->authorizationHeader($user))
                ->getJson('/api/recommendations')
                ->assertOk()
                ->assertJsonPath('success', true);

            $this->assertSame(6, count($response->json('data')));
            $this->assertSame(
                $scenario['expectedAcademic'],
                collect($response->json('data'))->where('nodala_id', $academic->Nodala_ID)->count()
            );

            if ($scenarioIndex === 0) {
                foreach (array_slice($books['entertainment'], 2, 2) as $book) {
                    DB::table('LietotajGramatas')->insert([
                        'Lietotajs' => $user->kodsID,
                        'Gramatas' => $book->ISBN,
                        'statuss' => 'lasu',
                        'pievienosanas_datums' => now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $updatedResponse = $this->withHeaders($this->authorizationHeader($user))
                    ->getJson('/api/recommendations')
                    ->assertOk()
                    ->assertJsonPath('success', true);

                $this->assertSame(
                    3,
                    collect($updatedResponse->json('data'))->where('nodala_id', $academic->Nodala_ID)->count()
                );
            }
        }
    }

    /**
     * Šis tests pārbauda, ka lietotājs saņem grāmatas no tās nodaļas, kuru viņam ir visvairāk bibliotēkā.
     * $preferredNodala un $otherNodala - izveido divas nodaļas ar dažādiem grāmatu komplektiem.
     * $recommendedPreferredBook - izveido grāmatu, kas pieder lietotāja iecienītajai nodaļai.
     * $this->withHeaders(...)->getJson('/api/recommendations') - pieprasa ieteikumus.
     * Tiek gaidīts, ka atgriezīsies grāmata no iecienītākās nodaļas un nevis grāmata no citas nodaļas.
     */
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
            ->assertJsonFragment(['nosaukums' => $ownedPreferredBook->nosaukums])
            ->assertJsonFragment(['nosaukums' => $ownedOtherBook->nosaukums]);
    }

    public function test_preferred_nodala_books_stay_ahead_of_more_popular_books_from_other_sections(): void
    {
        $user = $this->createUser();
        $academic = Nodala::create(['tips' => 'akademiska']);
        $entertainment = Nodala::create(['tips' => 'izglitojosa']);

        $createBookForSection = function (string $title, Nodala $section, int $isbn): Gramata {
            $genre = Zanrs::create([
                'nosaukums' => 'Genre ' . uniqid(),
                'Nodala' => $section->Nodala_ID,
            ]);

            return Gramata::create([
                'ISBN' => $isbn,
                'nosaukums' => $title,
                'autors' => 'Test Author',
                'Zanra_ID' => $genre->Zanra_ID,
                'Nodala_ID' => $section->Nodala_ID,
            ]);
        };

        $ownedAcademicBooks = [
            $createBookForSection('Owned academic 1', $academic, 8100000001),
            $createBookForSection('Owned academic 2', $academic, 8100000002),
        ];
        $academicRecommendations = [
            $createBookForSection('Academic recommendation 1', $academic, 8100000003),
            $createBookForSection('Academic recommendation 2', $academic, 8100000004),
        ];
        $entertainmentRecommendations = [];
        for ($index = 1; $index <= 5; $index++) {
            $book = $createBookForSection('Entertainment recommendation ' . $index, $entertainment, 8200000000 + $index);
            $entertainmentRecommendations[] = $book;

            DB::table('Parskata')->insert([
                'parskatas_skaits' => 1000,
                'Gramatas' => $book->ISBN,
                'Lietotajs' => $user->kodsID,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($ownedAcademicBooks as $book) {
            DB::table('LietotajGramatas')->insert([
                'Lietotajs' => $user->kodsID,
                'Gramatas' => $book->ISBN,
                'statuss' => 'lasu',
                'pievienosanas_datums' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->withHeaders($this->authorizationHeader($user))
            ->getJson('/api/recommendations')
            ->assertOk()
            ->assertJsonPath('success', true);

        $recommendations = $response->json('data');
        $this->assertSame($academic->Nodala_ID, $recommendations[0]['nodala_id']);
        $this->assertSame($academic->Nodala_ID, $recommendations[1]['nodala_id']);
        $this->assertSame($academic->Nodala_ID, $recommendations[2]['nodala_id']);
        $this->assertSame($academic->Nodala_ID, $recommendations[3]['nodala_id']);
        $this->assertSame($entertainment->Nodala_ID, $recommendations[4]['nodala_id']);
        $this->assertSame($entertainment->Nodala_ID, $recommendations[5]['nodala_id']);
        $this->assertCount(6, $recommendations);
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
