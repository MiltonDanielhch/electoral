<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreUserRequestTest extends TestCase
{
    public function test_validates_required_fields()
    {
        $rules = (new StoreUserRequest())->rules();
        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('person_id', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertArrayHasKey('role_id', $errors);
    }

    public function test_validates_email_format()
    {
        $rules = (new StoreUserRequest())->rules();
        $validator = Validator::make([
            'person_id' => 1,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 1,
            'email' => 'invalid-email'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_validates_password_min_length()
    {
        $rules = (new StoreUserRequest())->rules();
        $validator = Validator::make([
            'person_id' => 1,
            'password' => '123',
            'password_confirmation' => '123',
            'role_id' => 1,
            'email' => 'test@example.com'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validates_password_confirmation()
    {
        $rules = (new StoreUserRequest())->rules();
        $validator = Validator::make([
            'person_id' => 1,
            'password' => 'password123',
            'password_confirmation' => 'password456',
            'role_id' => 1,
            'email' => 'test@example.com'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_passes_with_valid_data()
    {
        $rules = (new StoreUserRequest())->rules();
        $data = [
            'person_id' => 1,
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 2,
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->fails());
    }
}
