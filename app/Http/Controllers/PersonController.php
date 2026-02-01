<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\User;
use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_people');
        return view('admin.people.browse');
    }

    public function list()
    {
        $this->custom_authorize('browse_people');

        $search   = request('search');
        $paginate = request('paginate', 10);

        $data = Person::query()
            ->search($search)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->paginate($paginate);

        return view('admin.people.list', compact('data'));
    }

    public function create()
    {
        $this->custom_authorize('add_people');
        return view('admin.people.edit-add', ['person' => new Person()]);
    }

    public function store(StorePersonRequest $request)
    {
        $this->custom_authorize('add_people');

        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['image'] = $request->hasFile('image') ? $this->storeImage($request->file('image')) : null;

            // Asignar status por defecto si no viene
            $data['status'] = 1;

            Person::create($data);

            DB::commit();
            return redirect()->route('admin.people.index')->with([
                'message' => 'Persona registrada exitosamente',
                'alert-type' => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('admin.people.index')->with([
                'message' => 'Ocurrió un error al guardar el registro.',
                'alert-type' => 'error'
            ]);
        }
    }

    public function edit($id)
    {
        $person = Person::findOrFail($id);
        $this->custom_authorize('edit_people');

        return view('admin.people.edit-add', compact('person'));
    }

    public function update(UpdatePersonRequest $request, $id)
    {
        $person = Person::findOrFail($id);
        $this->custom_authorize('edit_people');

        DB::beginTransaction();
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image'] = $this->storeImage($request->file('image'), $person->image);
            } elseif ($request->boolean('remove_image')) {
                if ($person->image) {
                    Storage::disk('public')->delete($person->image);
                }
                $data['image'] = null;
            }

            // Manejo del checkbox de estado
            $data['status'] = $request->has('status') ? 1 : 0;

            $person->update($data);

            DB::commit();
            return redirect()->route('admin.people.index')->with([
                'message' => 'Persona actualizada exitosamente',
                'alert-type' => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            // DEBUG: Muestra el error exacto que impide guardar. Si no ves nada, el problema es de validación.
            dd($th);
        }
    }

    public function show($id)
    {
        $this->custom_authorize('read_people');
        $person = Person::findOrFail($id);
        return view('admin.people.read', compact('person'));
    }

    public function destroy($id)
    {
        $person = Person::findOrFail($id);
        $this->custom_authorize('delete_people');

        // Aquí podrías agregar validaciones extra antes de eliminar
        // if ($person->tramites()->exists()) { ... }

        $person->delete();

        return redirect()->route('admin.people.index')->with([
            'message' => 'Persona eliminada correctamente',
            'alert-type' => 'success'
        ]);
    }

    /* ----------  MÉTODOS PRIVADOS  ---------- */

    private function storeImage($file, $old = null)
    {
        if ($old) {
            Storage::disk('public')->delete($old);
        }
        return $file ? $file->store('people', 'public') : null;
    }
}
