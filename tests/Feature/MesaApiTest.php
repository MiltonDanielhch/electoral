<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\Recinto;
use App\Models\Geografia;
use App\Models\ActaEscrutinio;
use App\Models\Cargo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MesaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_obtener_mesa_por_codigo_exitosamente()
    {
        $recinto = Recinto::factory()->municipio()->create();
        $mesa = Mesa::factory()->create([
            'id_recinto' => $recinto->id_recinto,
            'codigo_tse' => '12345678901',
        ]);

        $response = $this->getJson("/api/v1/mesa/{$mesa->codigo_tse}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id_mesa',
                    'codigo_tse',
                    'estado',
                    'recinto' => [
                        'id_recinto',
                        'nombre',
                        'direccion',
                        'geografia' => [
                            'id_geo',
                            'nombre',
                            'tipo',
                        ],
                    ],
                    'actas',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id_mesa' => $mesa->id_mesa,
                    'codigo_tse' => '12345678901',
                ],
            ]);
    }

    public function test_obtener_mesa_con_actas()
    {
        $recinto = Recinto::factory()->municipio()->create();
        $mesa = Mesa::factory()->create(['id_recinto' => $recinto->id_recinto]);
        $cargo = Cargo::factory()->create();

        ActaEscrutinio::factory()->create([
            'id_mesa' => $mesa->id_mesa,
            'id_cargo' => $cargo->id_cargo,
            'codigo_acta' => 'ACTA-001',
            'estado' => 'Digitada',
        ]);

        $response = $this->getJson("/api/v1/mesa/{$mesa->codigo_tse}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.actas')
            ->assertJsonPath('data.actas.0.codigo_acta', 'ACTA-001')
            ->assertJsonPath('data.actas.0.estado', 'Digitada');
    }

    public function test_obtener_mesa_no_existente_retorna_404()
    {
        $response = $this->getJson('/api/v1/mesa/99999999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Mesa no encontrada',
            ]);
    }

    public function test_obtener_mesa_incluye_informacion_completa_del_recinto()
    {
        $geografia = Geografia::factory()->create([
            'nombre' => 'Trinidad',
            'tipo' => 'Municipio',
        ]);
        $recinto = Recinto::factory()->create([
            'id_geografia' => $geografia->id_geografia,
            'nombre' => 'Unidad Educativa 1',
            'direccion' => 'Calle Principal 123',
        ]);
        $mesa = Mesa::factory()->create(['id_recinto' => $recinto->id_recinto]);

        $response = $this->getJson("/api/v1/mesa/{$mesa->codigo_tse}");

        $response->assertStatus(200)
            ->assertJsonPath('data.recinto.nombre', 'Unidad Educativa 1')
            ->assertJsonPath('data.recinto.direccion', 'Calle Principal 123')
            ->assertJsonPath('data.recinto.geografia.nombre', 'Trinidad')
            ->assertJsonPath('data.recinto.geografia.tipo', 'Municipio');
    }
}
