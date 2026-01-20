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
        $this->assertArrayHasKey('person_id', $validator->errors()->keys());
        $this->assertArrayHasKey('email', $validator->errors()->keys());
        $this->assertArrayHasKey('password', $validator->errors()->keys());
        $this->assertArrayHasKey('role_id', $validator->errors()->keys());
    }

    public function test_validates_email_format()
    {
        $rules = (new StoreUserRequest())->rules();
        $validator = Validator::make([
            'email' => 'invalid-email'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->keys());
    }

    public function test_validates_password_min_length()
    {
        $rules = (new StoreUserRequest())->rules();
        $validator = Validator::make([
            'password' => '123',
            'password_confirmation' => '123'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->keys());
    }

    public function test_validates_password_confirmation()
    {
        $rules = (new StoreUserRequest())->rules();
        $validator = Validator::make([
            'password' => 'password123',
            'password_confirmation' => 'password456'
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->keys());
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
