<?php

namespace Tests\Feature;

use App\Models\Mesa;
use App\Models\Recinto;
use App\Models\Geografia;
use App\Models\Cargo;
use App\Models\OrganizacionPolitica;
use App\Models\ActaEscrutinio;
use App\Models\VotoXPartido;
use App\Models\AuditoriaActa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_crear_acta_exitosamente()
    {
        $geografia = Geografia::factory()->create();
        $recinto = Recinto::factory()->create(['id_geografia' => $geografia->id_geo]);
        $mesa = Mesa::factory()->create(['id_recinto' => $recinto->id_recinto]);
        $cargo = Cargo::factory()->create();
        $partido1 = OrganizacionPolitica::factory()->create(['estado' => 'Activo']);
        $partido2 = OrganizacionPolitica::factory()->create(['estado' => 'Activo']);

        $fotoFrontal = UploadedFile::fake()->image('acta_frontal.jpg');
        $fotoReverso = UploadedFile::fake()->image('acta_reverso.jpg');

        $data = [
            'codigo_mesa' => $mesa->codigo_tse,
            'id_cargo' => $cargo->id_cargo,
            'codigo_acta' => 'ACTA-TEST-001',
            'foto_frontal' => $fotoFrontal,
            'foto_reverso' => $fotoReverso,
            'total_sobres' => 100,
            'total_votantes' => 95,
            'votos_validos' => 90,
            'votos_blancos' => 5,
            'votos_nulos' => 5,
            'votos_impugnados' => 0,
            'digitador' => 'Juan Perez',
            'votos_partido' => [
                ['id_partido' => $partido1->id_partido, 'votos' => 50],
                ['id_partido' => $partido2->id_partido, 'votos' => 40],
            ],
        ];

        $response = $this->postJson('/api/v1/acta', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id_acta',
                    'codigo_acta',
                    'estado',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Acta registrada exitosamente',
                'data' => [
                    'codigo_acta' => 'ACTA-TEST-001',
                    'estado' => 'Digitada',
                ],
            ]);

        $this->assertDatabaseHas('actas_escrutinio', [
            'codigo_acta' => 'ACTA-TEST-001',
            'id_mesa' => $mesa->id_mesa,
            'id_cargo' => $cargo->id_cargo,
            'estado' => 'Digitada',
        ]);

        $this->assertDatabaseHas('votos_x_partido', [
            'id_partido' => $partido1->id_partido,
            'votos' => 50,
        ]);

        $this->assertDatabaseHas('auditoria_actas', [
            'accion' => 'CREACION',
            'usuario' => 'Juan Perez',
        ]);

        Storage::disk('public')->assertExists('actas/fotos/' . $fotoFrontal->hashName());
        Storage::disk('public')->assertExists('actas/fotos/' . $fotoReverso->hashName());
    }

    public function test_crear_acta_con_codigo_mesa_invalido_falla_validacion()
    {
        $cargo = Cargo::factory()->create();

        $data = [
            'codigo_mesa' => 'INVALIDO',
            'id_cargo' => $cargo->id_cargo,
            'codigo_acta' => 'ACTA-TEST-001',
            'total_sobres' => 100,
            'total_votantes' => 95,
            'votos_validos' => 90,
            'votos_blancos' => 5,
            'votos_nulos' => 5,
            'digitador' => 'Juan Perez',
            'votos_partido' => [
                ['id_partido' => 1, 'votos' => 50],
            ],
        ];

        $response = $this->postJson('/api/v1/acta', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codigo_mesa']);
    }

    public function test_crear_acta_sin_fotos_almacena_exitosamente()
    {
        $geografia = Geografia::factory()->create();
        $recinto = Recinto::factory()->create(['id_geografia' => $geografia->id_geo]);
        $mesa = Mesa::factory()->create(['id_recinto' => $recinto->id_recinto]);
        $cargo = Cargo::factory()->create();
        $partido1 = OrganizacionPolitica::factory()->create(['estado' => 'Activo']);

        $data = [
            'codigo_mesa' => $mesa->codigo_tse,
            'id_cargo' => $cargo->id_cargo,
            'codigo_acta' => 'ACTA-TEST-002',
            'total_sobres' => 50,
            'total_votantes' => 45,
            'votos_validos' => 40,
            'votos_blancos' => 5,
            'votos_nulos' => 5,
            'digitador' => 'Maria Lopez',
            'votos_partido' => [
                ['id_partido' => $partido1->id_partido, 'votos' => 40],
            ],
        ];

        $response = $this->postJson('/api/v1/acta', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'codigo_acta' => 'ACTA-TEST-002',
                ],
            ]);

        $acta = ActaEscrutinio::where('codigo_acta', 'ACTA-TEST-002')->first();
        $this->assertNull($acta->foto_frontal);
        $this->assertNull($acta->foto_reverso);
    }

    public function test_crear_acta_con_codigo_acta_duplicado_falla()
    {
        $geografia = Geografia::factory()->create();
        $recinto = Recinto::factory()->create(['id_geografia' => $geografia->id_geo]);
        $mesa = Mesa::factory()->create(['id_recinto' => $recinto->id_recinto]);
        $cargo = Cargo::factory()->create();
        $partido1 = OrganizacionPolitica::factory()->create(['estado' => 'Activo']);

        ActaEscrutinio::factory()->create([
            'codigo_acta' => 'ACTA-DUPLICADA',
            'id_mesa' => $mesa->id_mesa,
            'id_cargo' => $cargo->id_cargo,
        ]);

        $data = [
            'codigo_mesa' => $mesa->codigo_tse,
            'id_cargo' => $cargo->id_cargo,
            'codigo_acta' => 'ACTA-DUPLICADA',
            'total_sobres' => 50,
            'total_votantes' => 45,
            'votos_validos' => 40,
            'votos_blancos' => 5,
            'votos_nulos' => 5,
            'digitador' => 'Juan Perez',
            'votos_partido' => [
                ['id_partido' => $partido1->id_partido, 'votos' => 40],
            ],
        ];

        $response = $this->postJson('/api/v1/acta', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['codigo_acta']);
    }
}
