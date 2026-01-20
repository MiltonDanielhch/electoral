<?php

namespace App\Http\Controllers;

use App\Models\OrganizacionPolitica;
use App\Http\Requests\StoreOrganizacionPoliticaRequest;
use App\Http\Requests\UpdateOrganizacionPoliticaRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrganizacionPoliticaController extends Controller
{
    use ManagesCrud;

    protected $model = OrganizacionPolitica::class;
    protected $browseView = 'admin.organizaciones_politicas.browse';
    protected $listView = 'admin.organizaciones_politicas.list';
    protected $with = [];

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('nombre', 'like', "%$search%")
            ->orWhere('sigla', 'like', "%$search%")
            ->orWhere('codigo_tse', 'like', "%$search%");
    }

    public function create()
    {
        $this->authorize('create', OrganizacionPolitica::class);
        return view('admin.organizaciones_politicas.edit-add', ['organizacion' => new OrganizacionPolitica()]);
    }

    public function store(StoreOrganizacionPoliticaRequest $request)
    {
        $this->authorize('create', OrganizacionPolitica::class);
        $data = $request->validated();

        if ($request->hasFile('logo_url')) {
            $data['logo_url'] = $request->file('logo_url')->store('organizaciones/logos', 'public');
        }

        OrganizacionPolitica::create($data);

        return redirect()->route('admin.organizaciones_politicas.index')
            ->with(['message' => 'Organización Política creada exitosamente.', 'alert-type' => 'success']);
    }

    public function edit(OrganizacionPolitica $organizacion)
    {
        $this->authorize('update', $organizacion);
        return view('admin.organizaciones_politicas.edit-add', compact('organizacion'));
    }

    public function update(UpdateOrganizacionPoliticaRequest $request, OrganizacionPolitica $organizacion)
    {
        $this->authorize('update', $organizacion);
        $data = $request->validated();

        if ($request->hasFile('logo_url')) {
            if ($organizacion->logo_url) {
                Storage::disk('public')->delete($organizacion->logo_url);
            }
            $data['logo_url'] = $request->file('logo_url')->store('organizaciones/logos', 'public');
        }

        $organizacion->update($data);

        return redirect()->route('admin.organizaciones_politicas.index')
            ->with(['message' => 'Organización Política actualizada exitosamente.', 'alert-type' => 'success']);
    }

    public function show(OrganizacionPolitica $organizacion)
    {
        $this->authorize('view', $organizacion);
        return view('admin.organizaciones_politicas.read', compact('organizacion'));
    }

    public function destroy(OrganizacionPolitica $organizacion)
    {
        $this->authorize('delete', $organizacion);

        if ($organizacion->candidatos()->exists()) {
            return back()->with(['message' => 'No se puede eliminar: tiene candidatos asociados.', 'alert-type' => 'error']);
        }

        try {
            if ($organizacion->logo_url) {
                Storage::disk('public')->delete($organizacion->logo_url);
            }
            $organizacion->delete();
            return redirect()->route('admin.organizaciones_politicas.index')
                ->with(['message' => 'Organización Política eliminada exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar organización {$organizacion->id_partido}: " . $e->getMessage());
            return redirect()->route('admin.organizaciones_politicas.index')
                ->with(['message' => 'Error al eliminar la organización política.', 'alert-type' => 'error']);
        }
    }
}
