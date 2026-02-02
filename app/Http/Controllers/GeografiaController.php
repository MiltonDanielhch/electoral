<?php

namespace App\Http\Controllers;

use App\Models\Geografia;
use App\Http\Requests\StoreGeografiaRequest;
use App\Http\Requests\UpdateGeografiaRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GeografiaController extends Controller
{
    use ManagesCrud;

    protected $model = Geografia::class;
    protected $browseView = 'admin.geografias.browse';
    protected $listView = 'admin.geografias.list';
    protected $with = ['parent'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('nombre', 'like', "%$search%")
            ->orWhere('codigo_tse', 'like', "%$search%");
    }

    public function ajaxParents(Request $request)
    {
        $tipo = $request->input('tipo');

        if (!$tipo) {
            return response()->json([]);
        }

        return response()->json(Geografia::where('tipo', $tipo)
            ->orderBy('nombre', 'ASC')
            ->get(['id_geografia', 'nombre', 'latitud', 'longitud']));
    }

    public function create()
    {
        $this->authorize('create', Geografia::class);
        $tipos = ['Departamento', 'Provincia', 'Municipio', 'Localidad'];
        $parents = Geografia::orderBy('nombre')->pluck('nombre', 'id_geografia');
        return view('admin.geografias.edit-add', ['geografia' => new Geografia(), 'tipos' => $tipos, 'parents' => $parents]);
    }

    public function store(StoreGeografiaRequest $request)
    {
        $this->authorize('create', Geografia::class);
        Geografia::create($request->validated());

        return redirect()->route('admin.geografias.index')
            ->with(['message' => 'Geografía creada exitosamente.', 'alert-type' => 'success']);
    }

    public function edit(Geografia $geografia)
    {
        $this->authorize('update', $geografia);
        $tipos = ['Departamento', 'Provincia', 'Municipio', 'Localidad'];
        $parents = Geografia::orderBy('nombre')->pluck('nombre', 'id_geografia');
        return view('admin.geografias.edit-add', compact('geografia', 'tipos', 'parents'));
    }

    public function update(UpdateGeografiaRequest $request, Geografia $geografia)
    {
        $this->authorize('update', $geografia);
        $geografia->update($request->validated());

        return redirect()->route('admin.geografias.index')
            ->with(['message' => 'Geografía actualizada exitosamente.', 'alert-type' => 'success']);
    }

    public function show(Geografia $geografia)
    {
        $this->authorize('view', $geografia);
        $geografia->load(['parent', 'children', 'recintos']);
        return view('admin.geografias.read', compact('geografia'));
    }

    public function mapaRecintos(Geografia $geografia)
    {
        $this->authorize('view', $geografia);
        $geografia->load(['parent', 'children', 'recintos', 'limite']);
        return view('admin.geografias.mapa-recintos', compact('geografia'));
    }

    public function destroy(Geografia $geografia)
    {
        $this->authorize('delete', $geografia);

        if ($geografia->children()->exists() || $geografia->recintos()->exists() || $geografia->candidatos()->exists()) {
            return back()->with(['message' => 'No se puede eliminar: tiene registros asociados.', 'alert-type' => 'error']);
        }

        $geografia->delete();
        return redirect()->route('admin.geografias.index')
            ->with(['message' => 'Geografía eliminada exitosamente.', 'alert-type' => 'success']);
    }
}
