<?php

namespace App\Http\Controllers;

use App\Models\Recinto;
use App\Models\Geografia;
use App\Http\Requests\StoreRecintoRequest;
use App\Http\Requests\UpdateRecintoRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

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
            Recinto::create($data);

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

        try {
            $recinto->update($data);

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
            $recinto->delete();
            return redirect()->route('admin.recintos.index')
                ->with(['message' => 'Recinto eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar recinto {$recinto->id_recinto}: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Error al eliminar el recinto.', 'alert-type' => 'error']);
        }
    }
}
