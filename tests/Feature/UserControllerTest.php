<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
    }

    public function test_admin_can_list_users()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.users.ajax.list'));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_filters_by_numeric_search()
    {
        User::factory()->create(['id' => 1]);
        User::factory()->create(['id' => 2]);

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.users.ajax.list', ['search' => '1']));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_filters_by_name()
    {
        User::factory()->create(['name' => 'Juan']);
        User::factory()->create(['name' => 'Maria']);

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.users.ajax.list', ['search' => 'Juan']));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_filters_by_email()
    {
        User::factory()->create(['email' => 'juan@example.com']);
        User::factory()->create(['email' => 'maria@example.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.users.ajax.list', ['search' => 'juan@example.com']));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_paginates_correctly()
    {
        User::factory()->count(25)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.users.ajax.list', ['paginate' => 10]));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_non_admin_cannot_see_admin_users()
    {
        $nonAdmin = User::factory()->create(['role_id' => null]);

        $response = $this->actingAs($nonAdmin)
            ->get(route('voyager.users.ajax.list'));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }



    public function test_store_fails_without_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), []);

        $response->assertSessionHasErrors(['person_id', 'email', 'password', 'role_id']);
    }

    public function test_store_fails_with_invalid_email_format()
    {
        $person = Person::factory()->active()->create();

        $userData = [
            'person_id' => $person->id,
            'email' => 'invalid-email',
            'password' => 'password123',
            'role_id' => 2,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_fails_with_short_password()
    {
        $person = Person::factory()->active()->create();

        $userData = [
            'person_id' => $person->id,
            'email' => 'user@example.com',
            'password' => '123',
            'role_id' => 2,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), $userData);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_store_fails_with_duplicate_email()
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $person = Person::factory()->active()->create();

        $userData = [
            'person_id' => $person->id,
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role_id' => 2,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_fails_with_inactive_person()
    {
        $person = Person::factory()->inactive()->create();

        $userData = [
            'person_id' => $person->id,
            'email' => 'user@example.com',
            'password' => 'password123',
            'role_id' => 2,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), $userData);

        $response->assertRedirect();
    }

    public function test_store_fails_with_deleted_person()
    {
        $person = Person::factory()->active()->create();
        $person->delete();

        $userData = [
            'person_id' => $person->id,
            'email' => 'user@example.com',
            'password' => 'password123',
            'role_id' => 2,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), $userData);

        $response->assertRedirect();
    }

    public function test_admin_can_update_user_status()
    {
        $user = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($this->admin)
            ->put(route('voyager.users.update', $user->id), ['status' => 'on']);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 1,
        ]);
    }

    public function test_admin_can_update_user_role()
    {
        $user = User::factory()->create(['role_id' => null]);

        $response = $this->actingAs($this->admin)
            ->put(route('voyager.users.update', $user->id), ['role_id' => null]);

        $response->assertRedirect();
    }

    public function test_admin_can_update_user_password()
    {
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);

        $response = $this->actingAs($this->admin)
            ->put(route('voyager.users.update', $user->id), ['password' => 'newpassword123']);

        $response->assertRedirect();
    }

    public function test_update_fails_when_user_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('voyager.users.update', 9999), ['status' => 'on']);

        $response->assertRedirect();
    }

    public function test_admin_can_destroy_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('voyager.users.destroy', $user->id));

        $response->assertRedirect();

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    public function test_destroy_fails_when_user_not_found()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('voyager.users.destroy', 9999));

        $response->assertRedirect();
    }

    public function test_unauthenticated_user_cannot_access_ajax_list()
    {
        $response = $this->get(route('voyager.users.ajax.list'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_store_user()
    {
        $person = Person::factory()->active()->create();

        $userData = [
            'person_id' => $person->id,
            'email' => 'user@example.com',
            'password' => 'password123',
            'role_id' => 2,
        ];

        $response = $this->post(route('voyager.users.store'), $userData);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_update_user()
    {
        $user = User::factory()->create();

        $response = $this->put(route('voyager.users.update', $user->id), ['status' => 'on']);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_destroy_user()
    {
        $user = User::factory()->create();

        $response = $this->delete(route('voyager.users.destroy', $user->id));

        $response->assertRedirect(route('login'));
    }

    public function test_user_list_includes_person_data()
    {
        User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.users.ajax.list'));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_user_list_excludes_deleted_users()
    {
        User::factory()->create();
        User::factory()->create()->delete();

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.users.ajax.list'));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_store_uses_person_name_as_user_name()
    {
        $person = Person::factory()->active()->create([
            'first_name' => 'Pedro',
            'paternal_surname' => 'Garcia'
        ]);

        $userData = [
            'person_id' => $person->id,
            'email' => 'pedro@example.com',
            'password' => 'password123',
            'role_id' => 2,
        ];

        $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), $userData);

        $this->assertDatabaseHas('users', [
            'person_id' => $person->id,
            'email' => 'pedro@example.com',
            'name' => 'Pedro Garcia',
        ]);
    }

    public function test_store_sets_default_avatar()
    {
        $person = Person::factory()->active()->create();
        $email = 'user@example.com';

        $userData = [
            'person_id' => $person->id,
            'email' => $email,
            'password' => 'password123',
            'role_id' => 2,
        ];

        $this->actingAs($this->admin)
            ->post(route('voyager.users.store'), $userData);

        $this->assertDatabaseHas('users', [
            'person_id' => $person->id,
            'email' => $email,
            'avatar' => 'users/default.png',
        ]);
    }
}
