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

    private function tokenFor(Lietotajs $user): string
    {
        return $user->kodsID . '_' . time();
    }

    private function headersFor(Lietotajs $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->tokenFor($user)];
    }
}
