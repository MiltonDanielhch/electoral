<?php

namespace Tests\Unit;

use App\Models\Person;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_person_belongs_to_user()
    {
        $user = User::factory()->create();

        $person = Person::factory()->create();
        $user->update(['person_id' => $person->id]);

        $this->assertInstanceOf(User::class, $person->user);
        $this->assertEquals($person->id, $person->user->person_id);
    }

    public function test_full_name_accessor_with_all_names()
    {
        $person = Person::factory()->create([
            'first_name' => 'Juan',
            'middle_name' => 'Carlos',
            'paternal_surname' => 'Perez',
            'maternal_surname' => 'Garcia',
        ]);

        $this->assertEquals('Juan Carlos Perez Garcia', $person->full_name);
    }

    public function test_full_name_accessor_without_middle_name()
    {
        $person = Person::factory()->create([
            'first_name' => 'Maria',
            'middle_name' => null,
            'paternal_surname' => 'Lopez',
            'maternal_surname' => 'Diaz',
        ]);

        $this->assertEquals('Maria Lopez Diaz', $person->full_name);
    }

    public function test_full_name_accessor_without_maternal_surname()
    {
        $person = Person::factory()->create([
            'first_name' => 'Carlos',
            'middle_name' => null,
            'paternal_surname' => 'Sanchez',
            'maternal_surname' => null,
        ]);

        $this->assertEquals('Carlos Sanchez', $person->full_name);
    }

    public function test_ci_mutator_formats_properly()
    {
        $ci = '1234567';
        $person = Person::factory()->create(['ci' => $ci]);

        $this->assertEquals($ci, $person->ci);
    }

    public function test_scope_active_filters_only_active_people()
    {
        Person::factory()->active()->create();
        Person::factory()->inactive()->create();
        Person::factory()->pending()->create();

        $active = Person::active()->get();

        $this->assertCount(1, $active);
        $this->assertTrue($active->first()->status === Person::STATUS_ACTIVE);
    }

    public function test_scope_active_returns_empty_when_no_active_people()
    {
        Person::factory()->inactive()->create();
        Person::factory()->pending()->create();

        $active = Person::active()->get();

        $this->assertCount(0, $active);
    }





    public function test_status_active_label()
    {
        $label = Person::getStatusLabel(Person::STATUS_ACTIVE);
        $this->assertEquals('Activo', $label);
    }

    public function test_status_inactive_label()
    {
        $label = Person::getStatusLabel(Person::STATUS_INACTIVE);
        $this->assertEquals('Inactivo', $label);
    }

    public function test_status_pending_label()
    {
        $label = Person::getStatusLabel(Person::STATUS_PENDING);
        $this->assertEquals('Pendiente', $label);
    }

    public function test_status_unknown_label()
    {
        $label = Person::getStatusLabel(99);
        $this->assertEquals('Desconocido', $label);
    }

    public function test_person_factory_creates_valid_person()
    {
        $person = Person::factory()->create();

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'ci' => $person->ci,
            'first_name' => $person->first_name,
            'paternal_surname' => $person->paternal_surname,
            'gender' => $person->gender,
        ]);
    }

    public function test_person_is_deletable()
    {
        $person = Person::factory()->create();

        $person->delete();

        $this->assertSoftDeleted('people', [
            'id' => $person->id,
        ]);
    }

    public function test_person_uses_soft_deletes()
    {
        $person = Person::factory()->create();

        $person->delete();

        $this->assertNotNull($person->deleted_at);
    }

    public function test_person_factory_active_state()
    {
        $person = Person::factory()->active()->create();

        $this->assertEquals(Person::STATUS_ACTIVE, $person->status);
    }

    public function test_person_factory_inactive_state()
    {
        $person = Person::factory()->inactive()->create();

        $this->assertEquals(Person::STATUS_INACTIVE, $person->status);
    }

    public function test_person_factory_pending_state()
    {
        $person = Person::factory()->pending()->create();

        $this->assertEquals(Person::STATUS_PENDING, $person->status);
    }
}
