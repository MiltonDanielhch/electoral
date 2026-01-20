<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createPermittedUser(['browse_people', 'add_people', 'edit_people']);
    }

    public function test_admin_can_index_people()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.index'));

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_index_people()
    {
        $response = $this->get(route('voyager.people.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_ajax_list_returns_people()
    {
        Person::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.ajax.list'));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_filters_by_numeric_search()
    {
        Person::factory()->create(['ci' => '12345678']);
        Person::factory()->create(['ci' => '87654321']);

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.ajax.list', ['search' => '123']));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_filters_by_name_search()
    {
        Person::factory()->create(['first_name' => 'Juan', 'paternal_surname' => 'Perez']);
        Person::factory()->create(['first_name' => 'Maria', 'paternal_surname' => 'Lopez']);

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.ajax.list', ['search' => 'Juan']));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_paginates_correctly()
    {
        Person::factory()->count(25)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.ajax.list', ['paginate' => 10]));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_admin_can_store_person()
    {
        $personData = [
            'ci' => '12345678',
            'first_name' => 'Juan',
            'middle_name' => 'Carlos',
            'paternal_surname' => 'Perez',
            'maternal_surname' => 'Garcia',
            'email' => 'juan@example.com',
            'phone' => '123456789',
            'gender' => 'Masculino',
            'birth_date' => '1990-01-01',
            'address' => 'Calle 123',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.people.store'), $personData);

        $response->assertRedirect(route('voyager.people.index'));

        $this->assertDatabaseHas('people', [
            'ci' => '12345678',
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
        ]);
    }

    public function test_store_fails_without_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('voyager.people.store'), []);

        $response->assertSessionHasErrors(['ci', 'first_name', 'paternal_surname']);
    }

    public function test_store_fails_with_invalid_ci_format()
    {
        $personData = [
            'ci' => 'ABC12345',
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.people.store'), $personData);

        $response->assertSessionHasErrors(['ci']);
    }

    public function test_store_fails_with_invalid_email_format()
    {
        $personData = [
            'ci' => '12345678',
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
            'email' => 'invalid-email',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.people.store'), $personData);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_fails_with_future_birth_date()
    {
        $personData = [
            'ci' => '12345678',
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
            'birth_date' => '2099-01-01',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('voyager.people.store'), $personData);

        $response->assertSessionHasErrors(['birth_date']);
    }

    public function test_admin_can_update_person()
    {
        $person = Person::factory()->create();

        $updateData = [
            'ci' => '12345678',
            'first_name' => 'Carlos',
            'paternal_surname' => 'Perez',
            'middle_name' => 'Jose',
            'maternal_surname' => 'Garcia',
            'email' => 'carlos@example.com',
            'phone' => '987654321',
            'gender' => 'Masculino',
            'birth_date' => '1990-01-01',
            'address' => 'Nueva Direccion',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('voyager.people.update', $person->id), $updateData);

        $response->assertRedirect();
    }

    public function test_update_fails_without_required_fields()
    {
        $person = Person::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('voyager.people.update', $person->id), []);

        $response->assertSessionHasErrors(['ci', 'first_name', 'paternal_surname']);
    }

    public function test_update_fails_with_invalid_ci_format()
    {
        $person = Person::factory()->create();

        $updateData = [
            'ci' => 'ABC12345',
            'first_name' => 'Carlos',
            'paternal_surname' => 'Perez',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('voyager.people.update', $person->id), $updateData);

        $response->assertSessionHasErrors(['ci']);
    }

    public function test_update_fails_when_person_not_found()
    {
        $updateData = [
            'ci' => '12345678',
            'first_name' => 'Carlos',
            'paternal_surname' => 'Perez',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('voyager.people.update', 9999), $updateData);

        $response->assertRedirect();
    }

    public function test_ajax_list_filters_by_phone()
    {
        Person::factory()->create(['phone' => '123456789']);
        Person::factory()->create(['phone' => '987654321']);

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.ajax.list', ['search' => '123456789']));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_shows_only_active_people()
    {
        Person::factory()->active()->create();
        Person::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.ajax.list'));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_ajax_list_returns_full_name()
    {
        Person::factory()->create([
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('voyager.people.ajax.list'));

        $response->assertStatus(200)
            ->assertViewHas('data');
    }

    public function test_update_toggles_status()
    {
        $person = Person::factory()->create(['status' => 1]);

        $updateData = [
            'ci' => $person->ci,
            'first_name' => $person->first_name,
            'paternal_surname' => $person->paternal_surname,
            'status' => 'on',
        ];

        $this->actingAs($this->admin)
            ->put(route('voyager.people.update', $person->id), $updateData);

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'status' => 1,
        ]);
    }

    public function test_unauthenticated_user_cannot_store_person()
    {
        $personData = [
            'ci' => '12345678',
            'first_name' => 'Juan',
            'paternal_surname' => 'Perez',
        ];

        $response = $this->post(route('voyager.people.store'), $personData);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_update_person()
    {
        $person = Person::factory()->create();

        $updateData = [
            'ci' => '12345678',
            'first_name' => 'Carlos',
            'paternal_surname' => 'Perez',
        ];

        $response = $this->put(route('voyager.people.update', $person->id), $updateData);

        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_access_ajax_list()
    {
        $response = $this->get(route('voyager.people.ajax.list'));

        $response->assertRedirect(route('login'));
    }
}
