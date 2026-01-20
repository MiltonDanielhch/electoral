# Estrategia de Pruebas - Fase 1

## Fecha: 2026-01-20

## Resumen

Este documento define la estrategia de implementación de pruebas para completar la Fase 1 del plan de desarrollo, enfocándose en pruebas unitarias y de funcionalidad para los modelos Person y User.

---

## Objetivos

1. **Pruebas Unitarias:** Validar la lógica interna de modelos Person y User
2. **Pruebas de Funcionalidad (Feature Tests):** Validar el flujo CRUD completo
3. **Cobertura Objetivo:** Mínimo 80% en modelos Person y User

---

## Configuración del Entorno de Pruebas

### Framework de Pruebas

El proyecto usa **PHPUnit** (incluido con Laravel).

### Estructura de Directorios

```
tests/
├── Unit/
│   ├── PersonTest.php          # Pruebas unitarias del modelo Person
│   ├── UserTest.php            # Pruebas unitarias del modelo User
│   └── ...
├── Feature/
│   ├── PersonControllerTest.php # Pruebas de funcionalidad CRUD Person
│   ├── UserControllerTest.php   # Pruebas de funcionalidad CRUD User
│   └── ...
└── TestCase.php                 # Clase base configurada
```

### Configuración phpunit.xml

Verificar que `tests/TestCase.php` tiene:

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
```

---

## Pruebas Unitarias - Modelo Person

### Archivo: `tests/Unit/PersonTest.php`

### Casos de Prueba a Implementar

#### 1. Relaciones

```php
public function test_person_belongs_to_user()
{
    $person = Person::factory()->has(User::factory())->create();
    
    $this->assertInstanceOf(User::class, $person->user);
    $this->assertEquals($person->id, $person->user->person_id);
}

public function test_person_has_one_user()
{
    $user = User::factory()->for(Person::factory())->create();
    
    $this->assertInstanceOf(Person::class, $user->person);
}
```

#### 2. Accessors y Mutators

```php
public function test_full_name_accessor()
{
    $person = Person::factory()->create([
        'nombres' => 'Juan',
        'apellidos' => 'Perez'
    ]);
    
    $this->assertEquals('Juan Perez', $person->full_name);
}

public function test_cedula_mutator_formats_properly()
{
    $person = Person::factory()->create(['cedula' => '1234567']);
    
    $this->assertEquals('1234567', $person->cedula);
}
```

#### 3. Scopes

```php
public function test_scope_active_filters_only_active()
{
    Person::factory()->create(['activo' => true]);
    Person::factory()->create(['activo' => false]);
    
    $active = Person::active()->get();
    
    $this->assertCount(1, $active);
    $this->assertTrue($active->first()->activo);
}
```

#### 4. Validación de Datos

```php
public function test_person_requires_valid_cedula()
{
    $this->expectException(\Illuminate\Database\QueryException::class);
    
    Person::factory()->create(['cedula' => null]);
}

public function test_person_requires_valid_email()
{
    $this->expectException(\Illuminate\Database\QueryException::class);
    
    Person::factory()->create(['email' => 'invalid-email']);
}
```

#### 5. Eventos de Auditoría

```php
public function test_person_registers_creation_event()
{
    Event::fake();
    
    $person = Person::factory()->create();
    
    Event::assertDispatched(\Illuminate\Auth\Events\Registered::class);
}
```

---

## Pruebas Unitarias - Modelo User

### Archivo: `tests/Unit/UserTest.php`

### Casos de Prueba a Implementar

#### 1. Relaciones

```php
public function test_user_belongs_to_person()
{
    $user = User::factory()->for(Person::factory())->create();
    
    $this->assertInstanceOf(Person::class, $user->person);
    $this->assertEquals($user->person_id, $user->person->id);
}

public function test_user_has_many_roles()
{
    $user = User::factory()->has(Role::factory()->count(2))->create();
    
    $this->assertCount(2, $user->roles);
}
```

#### 2. Autenticación y Contraseñas

```php
public function test_user_password_is_hashed()
{
    $password = 'secret123';
    $user = User::factory()->create(['password' => bcrypt($password)]);
    
    $this->assertNotEquals($password, $user->password);
    $this->assertTrue(Hash::check($password, $user->password));
}

public function test_user_can_authenticate()
{
    $user = User::factory()->create();
    
    $this->assertTrue(Auth::attempt(['email' => $user->email, 'password' => 'password']));
}
```

#### 3. Scopes

```php
public function test_scope_active_filters_only_active()
{
    User::factory()->create(['active' => true]);
    User::factory()->create(['active' => false]);
    
    $active = User::active()->get();
    
    $this->assertCount(1, $active);
}
```

#### 4. Casts

```php
public function test_email_is_lowercased()
{
    $user = User::factory()->create(['email' => 'TEST@EXAMPLE.COM']);
    
    $this->assertEquals('test@example.com', $user->email);
}
```

---

## Pruebas de Funcionalidad - Person CRUD

### Archivo: `tests/Feature/PersonControllerTest.php`

### Setup

```php
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
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }
}
```

### Casos de Prueba

#### 1. Listado de Personas (Browse)

```php
public function test_admin_can_browse_people()
{
    Person::factory()->count(10)->create();
    
    $response = $this->actingAs($this->admin)
        ->get(route('voyager.people.browse'));
    
    $response->assertStatus(200)
        ->assertViewHas('dataType')
        ->assertSeeInOrder(['nombres', 'apellidos', 'cedula']);
}
```

#### 2. Crear Persona (Store)

```php
public function test_admin_can_create_person()
{
    $personData = [
        'nombres' => 'Maria',
        'apellidos' => 'Gonzalez',
        'cedula' => '12345678',
        'email' => 'maria@example.com',
        'telefono' => '123456789',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('voyager.people.store'), $personData);
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('people', [
        'nombres' => 'Maria',
        'cedula' => '12345678',
    ]);
}
```

#### 3. Validación al Crear

```php
public function test_creation_fails_without_required_fields()
{
    $response = $this->actingAs($this->admin)
        ->post(route('voyager.people.store'), []);
    
    $response->assertSessionHasErrors(['nombres', 'apellidos', 'cedula']);
}
```

#### 4. Editar Persona (Update)

```php
public function test_admin_can_update_person()
{
    $person = Person::factory()->create();
    
    $response = $this->actingAs($this->admin)
        ->put(route('voyager.people.update', $person->id), [
            'nombres' => 'Carlos',
            'apellidos' => $person->apellidos,
            'cedula' => $person->cedula,
            'email' => $person->email,
        ]);
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('people', [
        'id' => $person->id,
        'nombres' => 'Carlos',
    ]);
}
```

#### 5. Eliminar Persona (Delete)

```php
public function test_admin_can_delete_person()
{
    $person = Person::factory()->create();
    
    $response = $this->actingAs($this->admin)
        ->delete(route('voyager.people.destroy', $person->id));
    
    $response->assertRedirect();
    
    $this->assertDatabaseMissing('people', ['id' => $person->id]);
}
```

#### 6. Búsqueda AJAX (List)

```php
public function test_ajax_search_returns_matching_people()
{
    Person::factory()->create(['nombres' => 'Juan', 'apellidos' => 'Perez']);
    Person::factory()->create(['nombres' => 'Maria', 'apellidos' => 'Lopez']);
    
    $response = $this->actingAs($this->admin)
        ->get(route('people.list', ['search' => 'Juan']));
    
    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['nombres' => 'Juan']);
}

public function test_ajax_search_with_pagination()
{
    Person::factory()->count(25)->create();
    
    $response = $this->actingAs($this->admin)
        ->get(route('people.list', ['page' => 1, 'per_page' => 10]));
    
    $response->assertStatus(200)
        ->assertJsonCount(10);
}
```

---

## Pruebas de Funcionalidad - User CRUD

### Archivo: `tests/Feature/UserControllerTest.php`

Estructura similar a PersonControllerTest, enfocado en:
- Creación de usuario con persona asociada
- Gestión de roles y permisos
- Reset de contraseña
- Activación/desactivación de cuenta

### Ejemplo de Caso de Prueba

```php
public function test_admin_can_create_user_with_person()
{
    $person = Person::factory()->create();
    
    $userData = [
        'person_id' => $person->id,
        'email' => 'user@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];
    
    $response = $this->actingAs($this->admin)
        ->post(route('voyager.users.store'), $userData);
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('users', [
        'person_id' => $person->id,
        'email' => 'user@example.com',
    ]);
}
```

---

## Pruebas de FormRequest

### Archivos de Prueba

Para cada FormRequest (StorePersonRequest, UpdatePersonRequest, etc.), crear pruebas que validen:

```php
<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StorePersonRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StorePersonRequestTest extends TestCase
{
    public function test_validates_required_fields()
    {
        $request = new StorePersonRequest();
        
        $validator = Validator::make([], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nombres', $validator->errors()->keys());
        $this->assertArrayHasKey('apellidos', $validator->errors()->keys());
        $this->assertArrayHasKey('cedula', $validator->errors()->keys());
    }
    
    public function test_validates_email_format()
    {
        $request = new StorePersonRequest();
        
        $validator = Validator::make([
            'email' => 'invalid-email'
        ], $request->rules());
        
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->keys());
    }
    
    public function test_passes_with_valid_data()
    {
        $request = new StorePersonRequest();
        
        $data = [
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'cedula' => '12345678',
            'email' => 'juan@example.com',
        ];
        
        $validator = Validator::make($data, $request->rules());
        
        $this->assertFalse($validator->fails());
    }
}
```

---

## Ejecución de Pruebas

### Comandos

```bash
# Ejecutar todas las pruebas
php artisan test

# Ejecutar solo pruebas unitarias
php artisan test --testsuite=Unit

# Ejecutar solo pruebas de funcionalidad
php artisan test --testsuite=Feature

# Ejecutar pruebas de un archivo específico
php artisan test tests/Unit/PersonTest.php

# Ver cobertura de código
php artisan test --coverage

# Ver detalles de errores
php artisan test --verbose
```

### Integración con CI/CD

En `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mbstring, pdo, pdo_mysql
      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist
      - name: Copy Environment
        run: cp .env.example .env
      - name: Generate Key
        run: php artisan key:generate
      - name: Run Migrations
        run: php artisan migrate --seed
      - name: Run Tests
        run: php artisan test
```

---

## Cronograma de Implementación

### Semana 1: Unit Tests
- [ ] PersonTest - Relaciones (1 día)
- [ ] PersonTest - Accessors y Scopes (1 día)
- [ ] UserTest - Relaciones y Autenticación (1 día)
- [ ] UserTest - Scopes y Casts (1 día)

### Semana 2: Feature Tests
- [ ] PersonControllerTest - CRUD básico (2 días)
- [ ] PersonControllerTest - Búsqueda AJAX (1 día)
- [ ] UserControllerTest - CRUD completo (2 días)

### Semana 3: FormRequest y Validación
- [ ] StorePersonRequestTest (1 día)
- [ ] UpdatePersonRequestTest (1 día)
- [ ] StoreUserRequestTest (1 día)
- [ ] UpdateUserRequestTest (1 día)
- [ ] Auditoría de StorageController (1 día)

### Semana 4: Integración y Cobertura
- [ ] Corregir pruebas fallidas (2 días)
- [ ] Alcanzar 80% cobertura (1 día)
- [ ] Integración CI/CD (1 día)
- [ ] Documentación de resultados (1 día)

---

## Métricas de Éxito

### Cobertura de Código
- Objetivo: 80% mínimo
- Person Model: >85%
- User Model: >85%
- Controllers Person/User: >75%

### Calidad de Pruebas
- Todas las pruebas pasan: 100%
- Sin pruebas pendientes (skipped)
- Sin pruebas marcadas como incompletas (incomplete)

### Rendimiento
- Suite completa: <30 segundos
- Unit tests: <10 segundos
- Feature tests: <20 segundos

---

## Herramientas Adicionales

### Para Análisis de Cobertura

```bash
# Instalar paquete de cobertura
composer require --dev phpunit/php-code-coverage

# Generar reporte HTML
php artisan test --coverage-html coverage
```

### Para Análisis Estático

```bash
# Instalar Larastan
composer require --dev larastan/larastan

# Ejecutar análisis
./vendor/bin/phpstan analyse
```

---

## Referencias

- Documentación PHPUnit: https://phpunit.de/documentation.html
- Documentación Testing Laravel: https://laravel.com/docs/10.x/testing
- Standard CRUD: `docs/plan/prompts2.md`
- Plan general: `docs/plan/plan.md`

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Alta  
**Fecha de Creación:** 2026-01-20  
**Responsable:** Equipo de Desarrollo
