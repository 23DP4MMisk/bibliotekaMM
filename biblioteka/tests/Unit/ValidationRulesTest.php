<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationRulesTest extends TestCase
{
    
    public function test_email_validation(): void
    {
        $rules = ['epasts' => 'required|email|max:100'];

        $valid = Validator::make(['epasts' => 'test@example.com'], $rules);
        $this->assertTrue($valid->passes());

        $invalidFormat = Validator::make(['epasts' => 'not-an-email'], $rules);
        $this->assertTrue($invalidFormat->fails());
        $this->assertArrayHasKey('epasts', $invalidFormat->errors()->toArray());

        $tooLong = Validator::make([
            'epasts' => str_repeat('a', 95) . '@x.com',
        ], $rules);
        $this->assertTrue($tooLong->fails());
        $this->assertArrayHasKey('epasts', $tooLong->errors()->toArray());
    }

    
    public function test_password_validation(): void
    {
        $rules = ['parole' => 'required|string|min:3'];

        $valid = Validator::make(['parole' => 'password123'], $rules);
        $this->assertTrue($valid->passes());

        $tooShort = Validator::make(['parole' => 'ab'], $rules);
        $this->assertTrue($tooShort->fails());
        $this->assertArrayHasKey('parole', $tooShort->errors()->toArray());
    }

    
    public function test_author_validation(): void
    {
        $rules = ['autors' => ['required', 'string', 'max:255', 'regex:/^[^\d]*$/']];

        $valid = Validator::make(['autors' => 'Jānis Rainis'], $rules);
        $this->assertTrue($valid->passes());

        foreach (['John123', 'Test Author 2020'] as $author) {
            $invalid = Validator::make(['autors' => $author], $rules);
            $this->assertTrue($invalid->fails());
            $this->assertArrayHasKey('autors', $invalid->errors()->toArray());
        }
    }

    
    public function test_isbn_validation(): void
    {
        $rules = ['ISBN' => 'required|integer'];

        $valid = Validator::make(['ISBN' => 1234567890], $rules);
        $this->assertTrue($valid->passes());

        $invalid = Validator::make(['ISBN' => 'abc'], $rules);
        $this->assertTrue($invalid->fails());
        $this->assertArrayHasKey('ISBN', $invalid->errors()->toArray());
    }

    
    public function test_year_validation(): void
    {
        $rules = ['gads' => 'nullable|string|size:4'];

        $valid = Validator::make(['gads' => '2020'], $rules);
        $this->assertTrue($valid->passes());

        foreach (['20', '20200'] as $year) {
            $invalid = Validator::make(['gads' => $year], $rules);
            $this->assertTrue($invalid->fails());
            $this->assertArrayHasKey('gads', $invalid->errors()->toArray());
        }
    }

    
    public function test_birth_date_validation(): void
    {
        $rules = ['dzim_datums' => 'nullable|date|before:today'];

        $valid = Validator::make(['dzim_datums' => '2000-01-01'], $rules);
        $this->assertTrue($valid->passes());

        $tomorrow = now()->addDay()->toDateString();
        $invalid = Validator::make(['dzim_datums' => $tomorrow], $rules);
        $this->assertTrue($invalid->fails());
        $this->assertArrayHasKey('dzim_datums', $invalid->errors()->toArray());
    }

    
    public function test_comment_validation(): void
    {
        $rules = ['komentars' => 'nullable|string|max:500'];

        $valid = Validator::make(['komentars' => str_repeat('a', 100)], $rules);
        $this->assertTrue($valid->passes());

        $tooLong = Validator::make(['komentars' => str_repeat('a', 501)], $rules);
        $this->assertTrue($tooLong->fails());
        $this->assertArrayHasKey('komentars', $tooLong->errors()->toArray());
    }
}