<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePersonRequestTest extends TestCase
{
    public function test_validates_required_fields()
    {
        $rules = (new StorePersonRequest())->rules();
        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ci', $validator->errors()->keys());
        $this->assertArrayHasKey('first_name', $validator->errors()->keys());
        $this->assertArrayHasKey('paternal_surname', $validator->errors()->keys());
    }

    public function test_validates_ci_format()
    {
        $rules = (new StorePersonRequest())->rules();
        $validator = Validator::make([
            'ci' => 'ABC12345'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ci', $validator->errors()->keys());
    }

    public function test_validates_ci_length()
    {
        $rules = (new StorePersonRequest())->rules();
        $validator = Validator::make([
            'ci' => '123456',
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('ci', $validator->errors()->keys());
    }

    public function test_validates_email_format()
    {
        $rules = (new StorePersonRequest())->rules();
        $validator = Validator::make([
            'email' => 'invalid-email'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->keys());
    }

    public function test_passes_with_valid_data()
    {
        $rules = (new StorePersonRequest())->rules();
        $data = [
            'ci' => '12345678',
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
            'email' => 'juan@example.com',
            'phone' => '123456789',
            'gender' => 'Masculino',
            'birth_date' => '1990-01-01',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->fails());
    }
}
