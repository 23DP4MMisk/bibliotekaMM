<?php

namespace Tests\Feature\Auth;

use App\Models\Lietotajs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'epasts' => 'reader@example.com',
            'lietotaja_vards' => 'BookReader',
            'parole' => 'secret123',
            'loma' => 'admins',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('Lietotajs', [
            'epasts' => 'reader@example.com',
            'lietotaja_vards' => 'BookReader',
        ]);

        $user = Lietotajs::where('epasts', 'reader@example.com')->firstOrFail();
        $this->assertNotSame('secret123', $user->parole);
        $this->assertTrue(Hash::check('secret123', $user->parole));
    }

   
    public function test_registration_fails_when_email_already_exists(): void
    {
        Lietotajs::create([
            'lietotaja_vards' => 'ExistingReader',
            'epasts' => 'reader@example.com',
            'parole' => Hash::make('secret123'),
            'loma' => 'registretajsklients',
            'registresanas_datums' => now()->toDateString(),
            'status' => 'aktivs',
        ]);

        $response = $this->postJson('/api/register', [
            'epasts' => 'reader@example.com',
            'lietotaja_vards' => 'AnotherReader',
            'parole' => 'secret123',
            'loma' => 'registretajsklients',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('epasts');
    }

    
    public function test_registration_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/register', [
            'epasts' => 'not-an-email',
            'lietotaja_vards' => 'BookReader',
            'parole' => 'secret123',
            'loma' => 'registretajsklients',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.epasts.0', 'Laukam e-pasts jābūt derīgai e-pasta adresei.');
    }

    
    public function test_registration_fails_without_password(): void
    {
        $response = $this->postJson('/api/register', [
            'epasts' => 'reader@example.com',
            'lietotaja_vards' => 'BookReader',
            'loma' => 'registretajsklients',
        ]);

        $response->assertStatus(422);
    }

    
    public function test_registration_fails_with_password_shorter_than_three_characters(): void
    {
        $response = $this->postJson('/api/register', [
            'epasts' => 'reader@example.com',
            'lietotaja_vards' => 'BookReader',
            'parole' => 'ab',
            'loma' => 'registretajsklients',
        ]);

        $response->assertStatus(422);
    }
}