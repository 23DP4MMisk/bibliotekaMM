<?php

namespace Tests\Unit;

use App\Models\Atsauksmes;
use App\Models\Gramata;
use App\Models\Lietotajs;
use App\Models\Nodala;
use App\Models\Zanrs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_gramata_belongs_to_zanrs(): void
    {
        $gramata = $this->createGramata();

        $this->assertInstanceOf(Zanrs::class, $gramata->zanrs);
    }

    
    public function test_gramata_belongs_to_nodala(): void
    {
        $gramata = $this->createGramata();

        $this->assertInstanceOf(Nodala::class, $gramata->nodala);
    }

    
    public function test_zanrs_has_many_gramatas(): void
    {
        $gramata = $this->createGramata();
        $zanrs = Zanrs::findOrFail($gramata->Zanra_ID);

        $this->assertTrue($zanrs->gramatas->contains('ISBN', $gramata->ISBN));
    }

    
    public function test_review_reply_belongs_to_parent_review(): void
    {
        [$parent, $reply] = $this->createReviewPair();

        $this->assertEquals($parent->Atsauksmes_ID, $reply->vecakais->Atsauksmes_ID);
    }

    
    public function test_parent_review_has_many_replies(): void
    {
        [$parent, $reply] = $this->createReviewPair();

        $this->assertTrue($parent->berni->contains($reply));
    }

   
    public function test_user_password_is_hidden_from_array_representation(): void
    {
        $user = $this->createUser();

        $this->assertArrayNotHasKey('parole', $user->toArray());
    }

    
    public function test_user_date_attributes_are_cast_to_carbon(): void
    {
        $user = $this->createUser();

        $this->assertInstanceOf(Carbon::class, $user->registresanas_datums);
        $this->assertInstanceOf(Carbon::class, $user->dzim_datums);
    }

    private function createGramata(): Gramata
    {
        $nodala = Nodala::create(['tips' => 'akademiska']);
        $zanrs = Zanrs::create([
            'nosaukums' => 'Test genre',
            'Nodala' => $nodala->Nodala_ID,
        ]);

        return Gramata::create([
            'ISBN' => '9780000000001',
            'nosaukums' => 'Test book',
            'autors' => 'Test author',
            'Zanra_ID' => $zanrs->Zanra_ID,
            'Nodala_ID' => $nodala->Nodala_ID,
        ]);
    }

    private function createReviewPair(): array
    {
        $user = $this->createUser();
        $gramata = $this->createGramata();

        $parent = Atsauksmes::create([
            'Lietotaja_ID' => $user->kodsID,
            'Gramatas_ID' => $gramata->ISBN,
            'vertejums' => 5,
        ]);

        $reply = Atsauksmes::create([
            'Lietotaja_ID' => $user->kodsID,
            'Gramatas_ID' => $gramata->ISBN,
            'vertejums' => 4,
            'vecakais_komentars' => $parent->Atsauksmes_ID,
        ]);

        return [$parent, $reply];
    }

    private function createUser(): Lietotajs
    {
        return Lietotajs::create([
            'lietotaja_vards' => 'TestReader',
            'epasts' => 'reader@example.com',
            'parole' => Hash::make('password123'),
            'loma' => 'registretajsklients',
            'registresanas_datums' => now()->toDateString(),
            'dzim_datums' => '2000-01-01',
            'status' => 'aktivs',
        ]);
    }
}