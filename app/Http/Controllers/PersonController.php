<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\User;
use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

        // Validación de CI Boliviano
        if ($request->tipo_doc === 'CI' && $request->ci) {
            if (!Person::validateBolivianCI($request->ci, $request->ci_complemento)) {
                return back()->withInput()->with([
                    'message' => 'El número de Carnet de Identidad no es válido. Verifique el número y el dígito verificador.',
                    'alert-type' => 'error'
                ]);
            }
        }

        // Verificar duplicados antes de guardar
        $duplicates = Person::potentialDuplicates(
            $request->ci,
            $request->first_name,
            $request->paternal_surname,
            $request->maternal_surname
        )->get();

        if ($duplicates->count() > 0) {
            $duplicateNames = $duplicates->map(fn($p) => $p->full_name . ' (CI: ' . $p->ci_formatted . ')')->implode(', ');
            return back()->withInput()->with([
                'message' => 'Atención: Posibles registros duplicados encontrados: ' . $duplicateNames . '. Verifique antes de continuar.',
                'alert-type' => 'warning'
            ]);
        }

        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Validar y almacenar imagen
            if ($request->hasFile('image')) {
                $validationResult = $this->validateImage($request->file('image'));
                if ($validationResult !== true) {
                    return back()->withInput()->with([
                        'message' => $validationResult,
                        'alert-type' => 'error'
                    ]);
                }
                $data['image'] = $this->storeImage($request->file('image'));
            }

            // Asignar status por defecto si no viene
            $data['status'] = 1;
            
            // Registrar usuario que crea
            $data['registerUser_id'] = auth()->id();
            $data['registerRole'] = auth()->user()->role->name ?? 'Usuario';

            Person::create($data);

            DB::commit();
            return redirect()->route('admin.people.index')->with([
                'message' => 'Persona registrada exitosamente',
                'alert-type' => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error creando persona: ' . $th->getMessage());
            return redirect()->route('admin.people.index')->with([
                'message' => 'Ocurrió un error al guardar el registro: ' . $th->getMessage(),
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

        // Validación de CI Boliviano (si cambió el CI)
        if ($request->tipo_doc === 'CI' && $request->ci && $request->ci !== $person->ci) {
            if (!Person::validateBolivianCI($request->ci, $request->ci_complemento)) {
                return back()->withInput()->with([
                    'message' => 'El número de Carnet de Identidad no es válido. Verifique el número y el dígito verificador.',
                    'alert-type' => 'error'
                ]);
            }
        }

        // Verificar duplicados antes de guardar (excluyendo el registro actual)
        if ($request->ci !== $person->ci || $request->first_name !== $person->first_name) {
            $duplicates = Person::potentialDuplicates(
                $request->ci,
                $request->first_name,
                $request->paternal_surname,
                $request->maternal_surname,
                $person->id
            )->get();

            if ($duplicates->count() > 0) {
                $duplicateNames = $duplicates->map(fn($p) => $p->full_name . ' (CI: ' . $p->ci_formatted . ')')->implode(', ');
                return back()->withInput()->with([
                    'message' => 'Atención: Posibles registros duplicados encontrados: ' . $duplicateNames . '. Verifique antes de continuar.',
                    'alert-type' => 'warning'
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $data = $request->validated();

            // Validar y almacenar imagen
            if ($request->hasFile('image')) {
                $validationResult = $this->validateImage($request->file('image'));
                if ($validationResult !== true) {
                    return back()->withInput()->with([
                        'message' => $validationResult,
                        'alert-type' => 'error'
                    ]);
                }
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
            Log::error('Error actualizando persona: ' . $th->getMessage());
            return redirect()->route('admin.people.index')->with([
                'message' => 'Ocurrió un error al actualizar el registro: ' . $th->getMessage(),
                'alert-type' => 'error'
            ]);
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

    /**
     * Valida una imagen antes de almacenarla
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return true|string True si es válida, mensaje de error si no
     */
    private function validateImage($file)
    {
        // Validar tamaño máximo (2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB en bytes
        if ($file->getSize() > $maxSize) {
            return 'La imagen no debe superar los 2MB.';
        }

        // Validar tipos MIME permitidos
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return 'Formato de imagen no válido. Use JPG o PNG.';
        }

        // Validar dimensiones mínimas
        $dimensions = getimagesize($file->getPathname());
        if ($dimensions) {
            list($width, $height) = $dimensions;
            if ($width < 100 || $height < 100) {
                return 'La imagen debe tener al menos 100x100 píxeles.';
            }
            if ($width > 2000 || $height > 2000) {
                return 'La imagen no debe superar 2000x2000 píxeles.';
            }
        }

        return true;
    }

    private function storeImage($file, $old = null)
    {
        if ($old) {
            Storage::disk('public')->delete($old);
        }
        return $file ? $file->store('people', 'public') : null;
    }
}
