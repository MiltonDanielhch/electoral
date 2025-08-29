<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_people');

        return view('administrations.people.browse');
    }

   public function list()
    {
        // Parámetros de entrada
        $search   = request('search');
        $paginate = request('paginate', 10);

        // Sub-consulta para el nombre completo
        $fullNameRaw = "TRIM(CONCAT(
            COALESCE(first_name, ''), ' ',
            COALESCE(middle_name, ''), ' ',
            COALESCE(paternal_surname, ''), ' ',
            COALESCE(maternal_surname, '')
        ))";

        // Consulta principal
        $data = Person::query()
            ->select('*')
            ->selectRaw("$fullNameRaw as full_name")
            ->when($search, function ($q) use ($search, $fullNameRaw) {
                // Búsqueda numérica exacta (id o ci)
                if (is_numeric($search)) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('id', $search)
                            ->orWhere('ci', 'like', "%{$search}%");
                    });
                }

                // Búsqueda textual parcial
                $q->orWhere(function ($sub) use ($search, $fullNameRaw) {
                    $sub->where('phone', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('paternal_surname', 'like', "%{$search}%")
                        ->orWhere('maternal_surname', 'like', "%{$search}%")
                        ->orWhereRaw("{$fullNameRaw} like ?", ["%{$search}%"]);
                });
            })
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->paginate($paginate);

        return view('administrations.people.list', compact('data'));
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_people');
        $request->validate([
            'image' => 'image|mimes:jpeg,jpg,png,bmp,webp'
        ]);
        DB::beginTransaction();
        try {
            // Si envian las imágenes
            $storageController = new StorageController();
            Person::create([
                'ci' => $request->ci,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'paternal_surname' => $request->paternal_surname,
                'maternal_surname' => $request->maternal_surname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'image' => $storageController->store_image($request->image, 'people'),
            ]);

            DB::commit();
            return redirect()->route('voyager.people.index')->with(['message' => 'Registrado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.people.index')->with(['message' => $th->getMessage(), 'alert-type' => 'error']);
        }
    }


    public function update(Request $request, $id){
        $this->custom_authorize('edit_people');
        $request->validate([
            'image' => 'image|mimes:jpeg,jpg,png,bmp,webp'
        ]);

        DB::beginTransaction();
        try {
            $storageController = new StorageController();

            $person = Person::find($id);
            $person->ci = $request->ci;
            $person->birth_date = $request->birth_date;
            $person->gender = $request->gender;
            $person->first_name = $request->first_name;
            $person->middle_name = $request->middle_name;
            $person->paternal_surname = $request->paternal_surname;
            $person->maternal_surname = $request->maternal_surname;
            $person->email = $request->email;
            $person->phone = $request->phone;
            $person->address = $request->address;
            $person->status = $request->status=='on' ? 1 : 0;

            if ($request->image) {
                $person->image = $storageController->store_image($request->image, 'people');
            }


            $person->save();

            DB::commit();
            return redirect()->route('voyager.people.index')->with(['message' => 'Actualizada exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.people.index')->with(['message' => $th->getMessage(), 'alert-type' => 'error']);
        }
    }
}
