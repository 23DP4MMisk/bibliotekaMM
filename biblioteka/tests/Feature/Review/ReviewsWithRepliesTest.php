<?php

namespace Tests\Feature\Review;

use App\Models\Atsauksmes;
use App\Models\Gramata;
use App\Models\Lietotajs;
use App\Models\Nodala;
use App\Models\Zanrs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewsWithRepliesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Šis tests pārbauda scenāriju, kur viesis mēģina atbildēt uz esošu atsauksmi.
     * $book = $this->createBook(); - izveido jaunu grāmatu, pie kuras būs atsauksmes.
     * $parent = $this->createReview($book); - izveido sākotnējo atsauksmi, uz kuru viesis mēģina atbildēt.
     * $this->postJson('/api/reviews', [...]) - nosūta POST pieprasījumu uz atsauksmju API ar:
     *   - gramatas_id -> grāmatas identifikators,
     *   - komentars -> teksts, ko viesis mēģina ievietot,
     *   - vecakais_komentars -> atsauksmes ID, uz kuru tiek rakstīta atbilde.
     * Pēc tam tiek gaidīts statusa kods 401, jo viesa lietotājs nav autentificēts un nevar publicēt komentāru.
     */
    public function test_guest_cannot_create_review_reply(): void
    {
        $book = $this->createBook();
        $parent = $this->createReview($book);

        $this->postJson('/api/reviews', [
            'gramatas_id' => $book->ISBN,
            'komentars' => 'Reply to review',
            'vecakais_komentars' => $parent->Atsauksmes_ID,
        ])->assertStatus(401);
    }

    /**
     * Šis tests pārbauda, ka autentificēts lietotājs var atbildēt uz esošu atsauksmi.
     * $user = $this->createUser(); - izveido reālu lietotāju, kas ir autentificējams.
     * $book = $this->createBook(); - izveido grāmatu, kurai pieder atsauksme.
     * $parent = $this->createReview($book, $user); - izveido sākotnējo atsauksmi, uz kuru tiks rakstīta atbilde.
     * $this->withHeaders($this->headersFor($user))->postJson('/api/reviews', [...]) - nosūta atbildes pieprasījumu ar Bearer tokenu.
     * Pieprasījumam jāatgriež 200 un jāizveido jauns ieraksts datubāzē ar vecakais_komentars = vecākatsauksmes ID.
     */
    public function test_authenticated_user_can_create_reply_to_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $parent = $this->createReview($book, $user);

        $this->withHeaders($this->headersFor($user))
            ->postJson('/api/reviews', [
                'gramatas_id' => $book->ISBN,
                'komentars' => 'Reply to review',
                'vecakais_komentars' => $parent->Atsauksmes_ID,
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('Atsauksmes', [
            'Gramatas_ID' => $book->ISBN,
            'vecakais_komentars' => $parent->Atsauksmes_ID,
            'komentārs' => 'Reply to review',
        ]);
    }

    /**
     * Šis tests pārbauda grāmatas atsauksmju API atbildi.
     * $user = $this->createUser(); - izveido lietotāju.
     * $book = $this->createBook(); - izveido grāmatu.
     * $parent = $this->createReview($book, $user); - izveido vecāku atsauksmi.
     * $reply = Atsauksmes::create([... 'vecakais_komentars' => $parent->Atsauksmes_ID]) - izveido atbildi ar norādi uz vecāko komentāru.
     * $this->getJson('/api/books/' . $book->ISBN . '/reviews') - izsauc endpointu, kurš atgriež atsauksmes saistībā ar grāmatu.
     * Testa mērķis ir pārbaudīt, ka atgrieztajā datu kopā ir redzama gan vecākatsauksme, gan atbilde.
     */
    public function test_book_reviews_endpoint_returns_parent_and_reply(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $parent = $this->createReview($book, $user);
        $reply = Atsauksmes::create([
            'Lietotaja_ID' => $user->kodsID,
            'Gramatas_ID' => $book->ISBN,
            'vertejums' => 4,
            'komentārs' => 'Reply to review',
            'vecakais_komentars' => $parent->Atsauksmes_ID,
        ]);

        $this->getJson('/api/books/' . $book->ISBN . '/reviews')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('Atsauksmes', [
            'Atsauksmes_ID' => $reply->Atsauksmes_ID,
            'vecakais_komentars' => $parent->Atsauksmes_ID,
        ]);
    }

    /**
     * Šis tests pārbauda validācijas drošību, ja tiek mēģināts izveidot atbildi uz neesošu atsauksmi.
     * $user = $this->createUser(); - izveido autentificētu lietotāju.
     * $book = $this->createBook(); - izveido grāmatu.
     * $this->withHeaders($this->headersFor($user))->postJson('/api/reviews', [... 'vecakais_komentars' => 999999]) - mēģina izveidot atbildi, norādot neesošu vecāka komentāra ID.
     * Tiek gaidīts 422, jo API nedrīkst pieņemt atbildi bez derīga vecākkomentāra.
     */
    public function test_reply_without_valid_parent_review_is_rejected(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();

        $this->withHeaders($this->headersFor($user))
            ->postJson('/api/reviews', [
                'gramatas_id' => $book->ISBN,
                'komentars' => 'Invalid reply',
                'vecakais_komentars' => 999999,
            ])
            ->assertStatus(422);
    }

    /**
     * Izveido lietotāju, ko izmantot atsauksmju un atbilžu testiem.
     * Metode garantē, ka katram testam ir atšķirīgs lietotājs ar aktīvu statusu pēc noklusējuma.
     */
    private function createUser(string $role = 'registretajsklients', string $status = 'aktivs'): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'ReviewAuthor',
            'epasts' => 'review' . uniqid() . '@example.com',
            'parole' => Hash::make('password123'),
            'loma' => $role,
            'registresanas_datums' => now()->toDateString(),
            'status' => $status,
        ]);
    }

    /**
     * Izveido grāmatu testam, kas nepieciešams atsauksmju un atbilžu pārbaudei.
     * Metode dublē nepieciešamos datus, lai grāmata būtu validēta un saglabāta datubāzē.
     */
    private function createBook(): Gramata
    {
        $nodala = Nodala::create(['tips' => 'akademiska']);
        $zanrs = Zanrs::create([
            'nosaukums' => 'Review book genre ' . uniqid(),
            'Nodala' => $nodala->Nodala_ID,
        ]);

        return Gramata::create([
            'ISBN' => (string) (2000000000 + random_int(1, 999999999)),
            'nosaukums' => 'Review book ' . uniqid(),
            'autors' => 'Jānis Rainis',
            'Zanra_ID' => $zanrs->Zanra_ID,
            'Nodala_ID' => $nodala->Nodala_ID,
        ]);
    }

    /**
     * Izveido sākotnējo atsauksmi, uz kuru vēlāk var pievienot atbildi.
     * Metode ļauj norādīt konkrētu lietotāju vai izmantot jaunu lietotāju pēc noklusējuma.
     */
    private function createReview(Gramata $book, ?Lietotajs $user = null): Atsauksmes
    {
        $user ??= $this->createUser();

        return Atsauksmes::create([
            'Lietotaja_ID' => $user->kodsID,
            'Gramatas_ID' => $book->ISBN,
            'vertejums' => 5,
            'komentārs' => 'Original review',
        ]);
    }

    /**
     * Ģenerē lietotāja tokenu, kas atbilst aplikācijas pieņemtajam formātam ID_laiks.
     * Tas tiek izmantots, lai imitētu autentificētu pieprasījumu.
     */
    private function tokenFor(Lietotajs $user): string
    {
        return $user->kodsID . '_' . time();
    }

    /**
     * Izveido HTTP galvenes ar Bearer tokenu autentifikācijai.
     * Šīs galvenes tiek izmantotas testu pieprasījumos, lai simulētu reālu lietotāja sesiju.
     */
    private function headersFor(Lietotajs $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenFor($user)];
    }
}
