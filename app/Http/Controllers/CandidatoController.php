<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\OrganizacionPolitica;
use App\Models\Cargo;
use App\Models\Geografia;
use App\Http\Requests\StoreCandidatoRequest;
use App\Http\Requests\UpdateCandidatoRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CandidatoController extends Controller
{
    use ManagesCrud;

    protected $model = Candidato::class;
    protected $browseView = 'admin.candidatos.browse';
    protected $listView = 'admin.candidatos.list';
    protected $with = ['partido', 'cargo', 'geografiaPostulacion'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('nombre_completo', 'like', "%$search%")
            ->orWhere('ci', 'like', "%$search%")
            // MEJORA: Filtro Smart - Permitir buscar por sigla de partido
            ->orWhereHas('partido', function($q) use ($search) {
                $q->where('sigla', 'like', "%$search%")
                  ->orWhere('nombre', 'like', "%$search%");
            })
            // MEJORA: También buscar por descripción de cargo
            ->orWhereHas('cargo', function($q) use ($search) {
                $q->where('descripcion', 'like', "%$search%");
            });
    }

    public function create()
    {
        $this->authorize('create', Candidato::class);
        $partidos = OrganizacionPolitica::orderBy('nombre')->get();
        $cargos = Cargo::orderBy('descripcion')->get();
        $geografias = Geografia::where('tipo', 'Departamento')
            ->orderBy('nombre')
            ->get();
        $estados = ['Activo', 'Inactivo'];
        return view('admin.candidatos.edit-add', [
            'candidato' => new Candidato(),
            'partidos' => $partidos,
            'cargos' => $cargos,
            'geografias' => $geografias,
            'estados' => $estados,
        ]);
    }

    public function store(StoreCandidatoRequest $request)
    {
        $this->authorize('create', Candidato::class);
        $data = $request->validated();

        try {
            if ($request->hasFile('imagen')) {
                $data['imagen'] = $request->file('imagen')->store('candidatos', 'public');
            }

            Candidato::create($data);

            return redirect()->route('admin.candidatos.index')
                ->with(['message' => 'Candidato creado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al crear candidato: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Ocurrió un error al guardar el candidato.', 'alert-type' => 'error']);
        }
    }

    public function edit(Candidato $candidato)
    {
        $this->authorize('update', $candidato);
        $partidos = OrganizacionPolitica::orderBy('nombre')->get();
        $cargos = Cargo::orderBy('descripcion')->get();
        $geografias = Geografia::where('tipo', 'Departamento')
            ->orderBy('nombre')
            ->get();
        $estados = ['Activo', 'Inactivo'];
        return view('admin.candidatos.edit-add', compact('candidato', 'partidos', 'cargos', 'geografias', 'estados'));
    }

    public function update(UpdateCandidatoRequest $request, Candidato $candidato)
    {
        $this->authorize('update', $candidato);
        $data = $request->validated();

        try {
            // FIX: Race Condition - Envolver en transacción DB para asegurar integridad
            DB::transaction(function() use ($request, $candidato, &$data) {
                if ($request->hasFile('imagen')) {
                    if ($candidato->imagen) {
                        Storage::disk('public')->delete($candidato->imagen);
                    }
                    $data['imagen'] = $request->file('imagen')->store('candidatos', 'public');
                }

                $candidato->update($data);
            });

            return redirect()->route('admin.candidatos.index')
                ->with(['message' => 'Candidato actualizado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al actualizar candidato {$candidato->id_candidato}: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Ocurrió un error al actualizar el candidato.', 'alert-type' => 'error']);
        }
    }

    public function show(Candidato $candidato)
    {
        $this->authorize('view', $candidato);
        $candidato->load(['partido', 'cargo', 'geografiaPostulacion']);
        return view('admin.candidatos.read', compact('candidato'));
    }

    public function destroy(Candidato $candidato)
    {
        $this->authorize('delete', $candidato);

        try {
            $candidato->delete();
            return redirect()->route('admin.candidatos.index')
                ->with(['message' => 'Candidato eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar candidato {$candidato->id_candidato}: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Error al eliminar el candidato.', 'alert-type' => 'error']);
        }
    }
}
