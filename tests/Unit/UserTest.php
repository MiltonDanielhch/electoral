<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_belongs_to_person()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Person::class, $user->person);
        $this->assertEquals($user->person_id, $user->person->id);
    }

    public function test_user_password_is_hashed()
    {
        $password = 'secret123';
        $user = User::factory()->create(['password' => bcrypt($password)]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_user_can_authenticate()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->assertTrue(Auth::attempt([
            'email' => $user->email,
            'password' => 'password'
        ]));
    }

    public function test_user_factory_creates_valid_user()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
            'person_id' => $user->person_id,
        ]);
    }

    public function test_user_uses_soft_deletes()
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_is_deletable()
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertNotNull($user->deleted_at);
    }

    public function test_user_email_is_unique()
    {
        $user1 = User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_user_has_api_tokens()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->createToken('test-token'));
    }

    public function test_user_status_defaults_to_active()
    {
        $user = User::factory()->create();

        $this->assertEquals(1, $user->status);
    }

    public function test_user_remember_token_is_hidden()
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('remember_token', $user->toArray());
    }

    public function test_user_password_is_hidden()
    {
        $user = User::factory()->create();

        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_user_email_verified_at_is_cast_to_datetime()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    public function test_user_unverified_state_sets_null_email_verified_at()
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }

    public function test_user_has_name_attribute()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertIsString($user->name);
    }

    public function test_user_person_relationship_works()
    {
        $person = Person::factory()->create();
        $user = User::factory()->create(['person_id' => $person->id]);

        $this->assertEquals($person->id, $user->person->id);
        $this->assertEquals($person->ci, $user->person->ci);
    }

    public function test_user_can_have_role()
    {
        $user = User::factory()->create(['role_id' => null]);

        $this->assertNull($user->role_id);
    }

    public function test_user_registerUser_id_defaults_to_null()
    {
        $user = User::factory()->create();

        $this->assertNull($user->registerUser_id);
    }

    public function test_user_registerRole_defaults_to_null()
    {
        $user = User::factory()->create();

        $this->assertNull($user->registerRole);
    }
}
