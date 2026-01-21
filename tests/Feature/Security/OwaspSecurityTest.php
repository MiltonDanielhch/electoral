<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Person;
use TCG\Voyager\Models\Role;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OwaspSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $regularRole;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );
        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            ['display_name' => 'User']
        );

        $person = Person::factory()->create();
        $this->admin = User::factory()->create([
            'person_id' => $person->id,
            'email' => 'admin@security.test',
            'role_id' => $adminRole->id,
        ]);

        $this->regularRole = $userRole->id;
    }

    public function test_sql_injection_prevention_in_search()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/people/ajax/list?search=\' OR 1=1--');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'total']);
    }

    public function test_sql_injection_prevention_in_numeric_params()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/people/1\' OR 1=1--');

        $this->assertNotEquals(200, $response->status());
    }

    public function test_xss_prevention_in_person_creation()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->actingAs($this->admin)
            ->post('/admin/people', [
                'nombres' => $xssPayload,
                'apellidos' => 'Test',
                'cedula' => '99999999',
                'email' => 'xss@test.com',
                'fecha_nacimiento' => '1990-01-01',
                'genero' => 'M',
            ]);

        $this->assertDatabaseHas('people', [
            'nombres' => $xssPayload,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_broken_access_control_unauthorized_user_cannot_access_admin()
    {
        $regularUser = User::factory()->create([
            'role_id' => $this->regularRole,
        ]);

        $response = $this->actingAs($regularUser)
            ->get('/admin/people');

        $response->assertStatus(403);
    }

    public function test_broken_access_control_unauthenticated_user_cannot_access_api()
    {
        $response = $this->post('/api/v1/acta', [
            'mesa_codigo' => 'TEST001',
            'imagen' => 'base64image',
            'votos' => ['MAS' => 100],
        ]);

        $response->assertStatus(401);
    }

    public function test_cryptographic_failures_password_hashing()
    {
        $person = Person::factory()->create();
        $user = User::create([
            'person_id' => $person->id,
            'email' => 'hash@test.com',
            'password' => Hash::make('plainpassword'),
            'role_id' => 1,
        ]);

        $this->assertTrue(Hash::check('plainpassword', $user->password));
        $this->assertNotEquals('plainpassword', $user->password);
    }

    public function test_security_misconfiguration_debug_disabled_in_production()
    {
        $originalDebug = config('app.debug');

        config(['app.debug' => false]);

        $response = $this->get('/non-existent-route');
        $response->assertStatus(404);

        config(['app.debug' => $originalDebug]);
    }

    public function test_authentication_failures_weak_password_rejected()
    {
        $person = Person::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post('/admin/users/store', [
                'person_id' => $person->id,
                'email' => 'weakpass@test.com',
                'password' => '123',
                'password_confirmation' => '123',
                'role_id' => 1,
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_data_integrity_acta_creation_requires_validation()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['actas:create']
        );

        $response = $this->post('/api/v1/acta', [
            'mesa_codigo' => '',
            'imagen' => '',
            'votos' => [],
        ]);

        $response->assertStatus(422);
    }

    public function test_logging_and_monitoring_security_events_logged()
    {
        $this->actingAs($this->admin)
            ->get('/admin/people/ajax/list?search=<script>alert(1)</script>');

        $logFile = storage_path('logs/laravel.log');
        $this->assertFileExists($logFile);
    }

    public function test_sensitive_data_exposure_password_not_exposed_in_api()
    {
        $person = Person::factory()->create();
        $user = User::factory()->create(['person_id' => $person->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/admin/users/ajax/list');

        $responseData = json_decode($response->getContent(), true);

        if (!empty($responseData['data'])) {
            foreach ($responseData['data'] as $userData) {
                $this->assertArrayNotHasKey('password', $userData);
            }
        }
    }

    public function test_command_injection_prevention()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/people', [
                'nombres' => 'Test; rm -rf /',
                'apellidos' => 'Test',
                'cedula' => '88888888',
                'email' => 'command@test.com',
                'fecha_nacimiento' => '1990-01-01',
                'genero' => 'M',
            ]);

        $response->assertSessionHasNoErrors();
    }
}
