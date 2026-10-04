<?php

namespace Tests\Feature\Admin;

use App\Models\Gramata;
use App\Models\Lietotajs;
use App\Models\Nodala;
use App\Models\Zanrs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookCrudTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_admin_can_create_a_book(): void
    {
        $admin = $this->createUser();
        $data = $this->validBookData();

        $this->withHeaders($this->headersFor($admin))
            ->postJson('/api/admin/books', $data)
            ->assertStatus(201);

        $this->assertDatabaseHas('Gramata', [
            'ISBN' => $data['ISBN'],
            'nosaukums' => $data['nosaukums'],
        ]);
    }

    
    public function test_regular_user_cannot_create_a_book(): void
    {
        $user = $this->createUser('registretajsklients');

        $this->withHeaders($this->headersFor($user))
            ->postJson('/api/admin/books', $this->validBookData())
            ->assertStatus(403);
    }

    
    public function test_guest_cannot_create_a_book(): void
    {
        $this->postJson('/api/admin/books', $this->validBookData())
            ->assertStatus(403);
    }

    
    public function test_admin_can_update_a_book(): void
    {
        $admin = $this->createUser();
        $book = $this->createBook();

        $this->withHeaders($this->headersFor($admin))
            ->putJson('/api/admin/books/' . $book->ISBN, ['nosaukums' => 'Updated title'])
            ->assertStatus(200);

        $this->assertDatabaseHas('Gramata', [
            'ISBN' => $book->ISBN,
            'nosaukums' => 'Updated title',
        ]);
    }

    
    public function test_admin_can_delete_a_book(): void
    {
        $admin = $this->createUser();
        $book = $this->createBook();

        $this->withHeaders($this->headersFor($admin))
            ->deleteJson('/api/admin/books/' . $book->ISBN)
            ->assertStatus(200);

        $this->assertDatabaseMissing('Gramata', ['ISBN' => $book->ISBN]);
    }

    
    public function test_book_author_cannot_contain_digits(): void
    {
        $admin = $this->createUser();
        $data = $this->validBookData();
        $data['autors'] = 'John123';

        $this->withHeaders($this->headersFor($admin))
            ->postJson('/api/admin/books', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('autors');
    }

    
    public function test_isbn_must_be_unique(): void
    {
        $admin = $this->createUser();
        $book = $this->createBook();
        $data = $this->validBookData($book->ISBN);

        $this->withHeaders($this->headersFor($admin))
            ->postJson('/api/admin/books', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('ISBN');
    }

    
    public function test_book_title_is_required(): void
    {
        $admin = $this->createUser();
        $data = $this->validBookData();
        unset($data['nosaukums']);

        $this->withHeaders($this->headersFor($admin))
            ->postJson('/api/admin/books', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors('nosaukums');
    }

    private function createUser(string $loma = 'admins', string $status = 'aktivs'): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'TestAdmin',
            'epasts' => 'admin@example.com',
            'parole' => Hash::make('password123'),
            'loma' => $loma,
            'registresanas_datums' => now()->toDateString(),
            'status' => $status,
        ]);
    }

    private function createNodala(): Nodala
    {
        return Nodala::create(['tips' => 'akademiska']);
    }

    private function createZanrs(int $nodalaId): Zanrs
    {
        return Zanrs::create([
            'nosaukums' => 'Test genre ' . $nodalaId,
            'Nodala' => $nodalaId,
        ]);
    }

    private function createBook(string $isbn = '9780000000001'): Gramata
    {
        $data = $this->validBookData($isbn);

        return Gramata::create($data);
    }

    private function validBookData(string $isbn = '9780000000001'): array
    {
        $nodala = $this->createNodala();
        $zanrs = $this->createZanrs($nodala->Nodala_ID);

        return [
            'ISBN' => $isbn,
            'nosaukums' => 'Test book',
            'autors' => 'Jānis Rainis',
            'Zanra_ID' => $zanrs->Zanra_ID,
            'Nodala_ID' => $nodala->Nodala_ID,
        ];
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