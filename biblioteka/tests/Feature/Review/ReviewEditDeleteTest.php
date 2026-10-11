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

class ReviewEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Autors var rediģēt savu atsauksmi (tekstu un vērtējumu).
     */
    public function test_author_can_update_own_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $review = $this->createReview($book, $user);

        $this->withHeaders($this->headersFor($user))
            ->putJson('/api/reviews/' . $review->Atsauksmes_ID, [
                'komentars' => 'Updated review',
                'vertejums' => 3,
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('Atsauksmes', [
            'Atsauksmes_ID' => $review->Atsauksmes_ID,
            'komentārs' => 'Updated review',
            'vertejums' => 3,
        ]);
    }

    /**
     * Autors var rediģēt savu atbildi; atbildes vērtējums paliek null.
     */
    public function test_author_can_update_own_reply(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $parent = $this->createReview($book, $user);
        $reply = $this->createReply($book, $user, $parent);

        $this->withHeaders($this->headersFor($user))
            ->putJson('/api/reviews/' . $reply->Atsauksmes_ID, [
                'komentars' => 'Edited reply',
                'vertejums' => 4,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('Atsauksmes', [
            'Atsauksmes_ID' => $reply->Atsauksmes_ID,
            'komentārs' => 'Edited reply',
            'vertejums' => null,
            'vecakais_komentars' => $parent->Atsauksmes_ID,
        ]);
    }

    /**
     * Cita lietotāja komentāru rediģēt nedrīkst.
     */
    public function test_other_user_cannot_update_review(): void
    {
        $author = $this->createUser();
        $other = $this->createUser();
        $book = $this->createBook();
        $review = $this->createReview($book, $author);

        $this->withHeaders($this->headersFor($other))
            ->putJson('/api/reviews/' . $review->Atsauksmes_ID, [
                'komentars' => 'Hijacked',
            ])
            ->assertStatus(403);

        $this->assertDatabaseHas('Atsauksmes', [
            'Atsauksmes_ID' => $review->Atsauksmes_ID,
            'komentārs' => 'Original review',
        ]);
    }

    /**
     * Viesis nevar rediģēt komentārus.
     */
    public function test_guest_cannot_update_review(): void
    {
        $author = $this->createUser();
        $book = $this->createBook();
        $review = $this->createReview($book, $author);

        $this->putJson('/api/reviews/' . $review->Atsauksmes_ID, [
            'komentars' => 'Guest edit',
        ])->assertStatus(401);
    }

    /**
     * Rediģējot ar tukšu tekstu, API atgriež validācijas kļūdu.
     */
    public function test_update_requires_comment_text(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $review = $this->createReview($book, $user);

        $this->withHeaders($this->headersFor($user))
            ->putJson('/api/reviews/' . $review->Atsauksmes_ID, [
                'komentars' => '',
            ])
            ->assertStatus(422);
    }

    /**
     * Neesoša komentāra rediģēšana atgriež 404.
     */
    public function test_updating_missing_review_returns_404(): void
    {
        $user = $this->createUser();

        $this->withHeaders($this->headersFor($user))
            ->putJson('/api/reviews/999999', [
                'komentars' => 'Nothing',
            ])
            ->assertStatus(404);
    }

    /**
     * Autors var dzēst savu atsauksmi.
     */
    public function test_author_can_delete_own_review(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $review = $this->createReview($book, $user);

        $this->withHeaders($this->headersFor($user))
            ->deleteJson('/api/reviews/' . $review->Atsauksmes_ID)
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('Atsauksmes', [
            'Atsauksmes_ID' => $review->Atsauksmes_ID,
        ]);
    }

    /**
     * Dzēšot vecāko atsauksmi, tiek dzēstas arī tās atbildes.
     */
    public function test_deleting_review_also_deletes_its_replies(): void
    {
        $user = $this->createUser();
        $book = $this->createBook();
        $parent = $this->createReview($book, $user);
        $reply = $this->createReply($book, $user, $parent);

        $this->withHeaders($this->headersFor($user))
            ->deleteJson('/api/reviews/' . $parent->Atsauksmes_ID)
            ->assertStatus(200);

        $this->assertDatabaseMissing('Atsauksmes', [
            'Atsauksmes_ID' => $reply->Atsauksmes_ID,
        ]);
    }

    /**
     * Cita lietotāja komentāru dzēst nedrīkst.
     */
    public function test_other_user_cannot_delete_review(): void
    {
        $author = $this->createUser();
        $other = $this->createUser();
        $book = $this->createBook();
        $review = $this->createReview($book, $author);

        $this->withHeaders($this->headersFor($other))
            ->deleteJson('/api/reviews/' . $review->Atsauksmes_ID)
            ->assertStatus(403);

        $this->assertDatabaseHas('Atsauksmes', [
            'Atsauksmes_ID' => $review->Atsauksmes_ID,
        ]);
    }

    /**
     * Viesis nevar dzēst komentārus.
     */
    public function test_guest_cannot_delete_review(): void
    {
        $author = $this->createUser();
        $book = $this->createBook();
        $review = $this->createReview($book, $author);

        $this->deleteJson('/api/reviews/' . $review->Atsauksmes_ID)
            ->assertStatus(401);

        $this->assertDatabaseHas('Atsauksmes', [
            'Atsauksmes_ID' => $review->Atsauksmes_ID,
        ]);
    }

    private function createUser(): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'ReviewAuthor',
            'epasts' => 'review' . uniqid() . '@example.com',
            'parole' => Hash::make('password123'),
            'loma' => 'registretajsklients',
            'registresanas_datums' => now()->toDateString(),
            'status' => 'aktivs',
        ]);
    }

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

    private function createReview(Gramata $book, Lietotajs $user): Atsauksmes
    {
        return Atsauksmes::create([
            'Lietotaja_ID' => $user->kodsID,
            'Gramatas_ID' => $book->ISBN,
            'vertejums' => 5,
            'komentārs' => 'Original review',
        ]);
    }

    private function createReply(Gramata $book, Lietotajs $user, Atsauksmes $parent): Atsauksmes
    {
        return Atsauksmes::create([
            'Lietotaja_ID' => $user->kodsID,
            'Gramatas_ID' => $book->ISBN,
            'vertejums' => null,
            'komentārs' => 'Original reply',
            'vecakais_komentars' => $parent->Atsauksmes_ID,
        ]);
    }

    private function headersFor(Lietotajs $user): array
    {
        return ['Authorization' => 'Bearer ' . $user->kodsID . '_' . time()];
    }
}
