<?php

namespace App\Http\Controllers;

use App\Models\Recinto;
use App\Models\Geografia;
use App\Http\Requests\StoreRecintoRequest;
use App\Http\Requests\UpdateRecintoRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RecintoController extends Controller
{
    use ManagesCrud;

    protected $model = Recinto::class;
    protected $browseView = 'admin.recintos.browse';
    protected $listView = 'admin.recintos.list';
    protected $with = ['geografia'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('nombre', 'like', "%$search%")
            ->orWhere('codigo_tse', 'like', "%$search%")
            ->orWhere('direccion', 'like', "%$search%");
    }

    public function create()
    {
        $this->authorize('create', Recinto::class);
        $geografias = Geografia::where('tipo', 'Municipio')
            ->orderBy('nombre')
            ->get();
        return view('admin.recintos.edit-add', [
            'recinto' => new Recinto(),
            'geografias' => $geografias,
        ]);
    }

    public function store(StoreRecintoRequest $request)
    {
        $this->authorize('create', Recinto::class);
        $data = $request->validated();

        try {
            $recinto = Recinto::create($data);

            // Sintonía: Invalidar cachés relacionados al crear recinto
            $this->invalidarCacheRecinto($data['id_geografia'] ?? null);

            return redirect()->route('admin.recintos.index')
                ->with(['message' => 'Recinto creado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al crear recinto: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Ocurrió un error al guardar el recinto.', 'alert-type' => 'error']);
        }
    }

    public function edit(Recinto $recinto)
    {
        $this->authorize('update', $recinto);
        $geografias = Geografia::where('tipo', 'Municipio')
            ->orderBy('nombre')
            ->get();
        return view('admin.recintos.edit-add', compact('recinto', 'geografias'));
    }

    public function update(UpdateRecintoRequest $request, Recinto $recinto)
    {
        $this->authorize('update', $recinto);
        $data = $request->validated();
        
        // Guardar geografía anterior para invalidar caché si cambió
        $geoAnterior = $recinto->id_geografia;

        try {
            $recinto->update($data);

            // Sintonía: Invalidar cachés relacionados al actualizar recinto
            $this->invalidarCacheRecinto($data['id_geografia'] ?? null);
            
            // Si cambió de geografía, invalidar también la anterior
            if (isset($data['id_geografia']) && $geoAnterior != $data['id_geografia']) {
                $this->invalidarCacheRecinto($geoAnterior);
            }

            return redirect()->route('admin.recintos.index')
                ->with(['message' => 'Recinto actualizado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al actualizar recinto {$recinto->id_recinto}: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Ocurrió un error al actualizar el recinto.', 'alert-type' => 'error']);
        }
    }

    public function show(Recinto $recinto)
    {
        $this->authorize('view', $recinto);
        $recinto->load(['geografia', 'mesas']);
        return view('admin.recintos.read', compact('recinto'));
    }

    public function destroy(Recinto $recinto)
    {
        $this->authorize('delete', $recinto);

        if ($recinto->mesas()->exists()) {
            return back()->with(['message' => 'No se puede eliminar: tiene mesas asociadas.', 'alert-type' => 'error']);
        }

        try {
            $geoId = $recinto->id_geografia;
            $recinto->delete();
            
            // Sintonía: Invalidar cachés relacionados al eliminar recinto
            $this->invalidarCacheRecinto($geoId);
            
            return redirect()->route('admin.recintos.index')
                ->with(['message' => 'Recinto eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar recinto {$recinto->id_recinto}: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Error al eliminar el recinto.', 'alert-type' => 'error']);
        }
    }

    /**
     * Sintonía: Invalidar cachés relacionados con recintos
     * Se llama después de crear, actualizar o eliminar
     */
    private function invalidarCacheRecinto(?int $idGeografia = null): void
    {
        // Limpiar cachés específicos de mapas
        if ($idGeografia) {
            Cache::forget("recintos_por_geo:{$idGeografia}");
            Cache::forget("recintos_geojson:{$idGeografia}");
            Cache::forget("geo_recintos_count_{$idGeografia}");
            Cache::forget("geo_children_{$idGeografia}");
        }
        
        // Limpiar cachés globales
        Cache::forget('election_live_results');
        Cache::forget('cargos:all');
    }
}
