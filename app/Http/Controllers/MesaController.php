<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Recinto;
use App\Http\Requests\StoreMesaRequest;
use App\Http\Requests\UpdateMesaRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class MesaController extends Controller
{
    use ManagesCrud;

    protected $model = Mesa::class;
    protected $browseView = 'admin.mesas.browse';
    protected $listView = 'admin.mesas.list';
    protected $with = ['recinto.geografia'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('codigo_tse', 'like', "%$search%")
            ->orWhere('numero_mesa', 'like', "%$search%")
            ->orWhereHas('recinto', function($q) use ($search) {
                $q->where('nombre', 'like', "%$search%");
            });
    }

    public function create()
    {
        $this->authorize('create', Mesa::class);
        $recintos = Recinto::orderBy('nombre')->get();
        $estados = ['Habilitada', 'Escrutada', 'Anulada', 'Observada'];
        return view('admin.mesas.edit-add', [
            'mesa' => new Mesa(),
            'recintos' => $recintos,
            'estados' => $estados,
        ]);
    }

    public function store(StoreMesaRequest $request)
    {
        $this->authorize('create', Mesa::class);
        $data = $request->validated();

        try {
            Mesa::create($data);

            return redirect()->route('admin.mesas.index')
                ->with(['message' => 'Mesa creada exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al crear mesa: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Ocurrió un error al guardar la mesa.', 'alert-type' => 'error']);
        }
    }

    public function edit(Mesa $mesa)
    {
        $this->authorize('update', $mesa);
        $recintos = Recinto::orderBy('nombre')->get();
        $estados = ['Habilitada', 'Escrutada', 'Anulada', 'Observada'];
        return view('admin.mesas.edit-add', compact('mesa', 'recintos', 'estados'));
    }

    public function update(UpdateMesaRequest $request, Mesa $mesa)
    {
        $this->authorize('update', $mesa);
        $data = $request->validated();

        try {
            $mesa->update($data);

            return redirect()->route('admin.mesas.index')
                ->with(['message' => 'Mesa actualizada exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al actualizar mesa {$mesa->id_mesa}: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Ocurrió un error al actualizar la mesa.', 'alert-type' => 'error']);
        }
    }

    public function show(Mesa $mesa)
    {
        $this->authorize('view', $mesa);
        $mesa->load(['recinto.geografia', 'actasEscrutinio']);
        return view('admin.mesas.read', compact('mesa'));
    }

    public function destroy(Mesa $mesa)
    {
        $this->authorize('delete', $mesa);

        if ($mesa->actasEscrutinio()->exists()) {
            return back()->with(['message' => 'No se puede eliminar: tiene actas de escrutinio asociadas.', 'alert-type' => 'error']);
        }

        try {
            $mesa->delete();
            return redirect()->route('admin.mesas.index')
                ->with(['message' => 'Mesa eliminada exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar mesa {$mesa->id_mesa}: " . $e->getMessage());
            return back()->withInput()
                ->with(['message' => 'Error al eliminar la mesa.', 'alert-type' => 'error']);
        }
    }
}
