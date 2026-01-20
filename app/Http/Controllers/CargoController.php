<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class CargoController extends Controller
{
    use ManagesCrud;

    protected $model = Cargo::class;
    protected $browseView = 'admin.cargos.browse';
    protected $listView = 'admin.cargos.list';

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('descripcion', 'like', "%$search%")
            ->orWhere('nivel', 'like', "%$search%");
    }

    public function create()
    {
        $this->authorize('create', Cargo::class);
        return view('admin.cargos.edit-add', ['cargo' => new Cargo()]);
    }

    public function store(StoreCargoRequest $request)
    {
        $this->authorize('create', Cargo::class);
        Cargo::create($request->validated());

        return redirect()->route('admin.cargos.index')
            ->with(['message' => 'Cargo creado exitosamente.', 'alert-type' => 'success']);
    }

    public function edit(Cargo $cargo)
    {
        $this->authorize('update', $cargo);
        return view('admin.cargos.edit-add', compact('cargo'));
    }

    public function update(UpdateCargoRequest $request, Cargo $cargo)
    {
        $this->authorize('update', $cargo);
        $cargo->update($request->validated());

        return redirect()->route('admin.cargos.index')
            ->with(['message' => 'Cargo actualizado exitosamente.', 'alert-type' => 'success']);
    }

    public function show(Cargo $cargo)
    {
        $this->authorize('view', $cargo);
        return view('admin.cargos.read', compact('cargo'));
    }

    public function destroy(Cargo $cargo)
    {
        $this->authorize('delete', $cargo);

        if ($cargo->candidatos()->exists()) {
            return back()->with(['message' => 'No se puede eliminar: tiene candidatos asociados.', 'alert-type' => 'error']);
        }

        try {
            $cargo->delete();
            return redirect()->route('admin.cargos.index')
                ->with(['message' => 'Cargo eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar cargo {$cargo->id_cargo}: " . $e->getMessage());
            return redirect()->route('admin.cargos.index')
                ->with(['message' => 'Error al eliminar el cargo.', 'alert-type' => 'error']);
        }
    }
}
