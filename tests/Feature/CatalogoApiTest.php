<?php

namespace Tests\Feature;

use App\Models\OrganizacionPolitica;
use App\Models\Cargo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_catalogos_exitosamente()
    {
        OrganizacionPolitica::factory()->create(['estado' => 'Activo']);
        OrganizacionPolitica::factory()->create(['estado' => 'Inactivo']);
        Cargo::factory()->create();

        $response = $this->getJson('/api/v1/catalogos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'partidos_politicos' => [],
                    'cargos' => [],
                ],
            ])
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(1, 'data.partidos_politicos')
            ->assertJsonCount(1, 'data.cargos');
    }

    public function test_catalogos_retorna_solo_partidos_activos()
    {
        OrganizacionPolitica::factory()->count(3)->create(['estado' => 'Activo']);
        OrganizacionPolitica::factory()->count(2)->create(['estado' => 'Inactivo']);

        $response = $this->getJson('/api/v1/catalogos');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.partidos_politicos');
    }

    public function test_catalogos_incluye_campos_requeridos()
    {
        $partido = OrganizacionPolitica::factory()->create([
            'estado' => 'Activo',
            'codigo_tse' => 'MAS',
            'nombre' => 'Movimiento al Socialismo',
            'sigla' => 'MAS-IPSP',
            'color_hex' => '#005a9c',
        ]);

        $response = $this->getJson('/api/v1/catalogos');

        $response->assertStatus(200)
            ->assertJsonPath('data.partidos_politicos.0.id_partido', $partido->id_partido)
            ->assertJsonPath('data.partidos_politicos.0.codigo_tse', 'MAS')
            ->assertJsonPath('data.partidos_politicos.0.nombre', 'Movimiento al Socialismo')
            ->assertJsonPath('data.partidos_politicos.0.sigla', 'MAS-IPSP')
            ->assertJsonPath('data.partidos_politicos.0.color_hex', '#005a9c');
    }
}
