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
        $search   = request('search');
        $paginate = request('paginate', 10);

        $fullNameRaw = "TRIM(CONCAT(
            COALESCE(first_name, ''), ' ',
            COALESCE(middle_name, ''), ' ',
            COALESCE(paternal_surname, ''), ' ',
            COALESCE(maternal_surname, '')
        ))";

        $query = Person::query()
            ->select('id', 'ci', 'birth_date', 'phone', 'gender', 'status', 'image', 
                     'first_name', 'middle_name', 'paternal_surname', 'maternal_surname')
            ->selectRaw("$fullNameRaw as full_name")
            ->whereNull('deleted_at');

        if ($search) {
            if (is_numeric($search)) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('id', $search)
                        ->orWhere('ci', 'like', "%{$search}%");
                });
            } else {
                $query->where(function ($sub) use ($search, $fullNameRaw) {
                    $sub->where('phone', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('paternal_surname', 'like', "%{$search}%")
                        ->orWhere('maternal_surname', 'like', "%{$search}%")
                        ->orWhereRaw("{$fullNameRaw} like ?", ["%{$search}%"]);
                });
            }
        }

        $data = $query->orderByDesc('id')->paginate($paginate);

        return view('administrations.people.list', compact('data'));
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_people');

        $validated = $request->validate([
            'ci' => 'required|string|regex:/^[0-9]{7,10}$/',
            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:people|max:255',
            'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
            'gender' => 'nullable|string',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $storageController = new StorageController();

            $imagePath = $request->image
                ? $storageController->store_image($request->image, 'people')
                : null;

            Person::create([
                'ci' => $validated['ci'],
                'birth_date' => $validated['birth_date'],
                'gender' => $validated['gender'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'paternal_surname' => $validated['paternal_surname'],
                'maternal_surname' => $validated['maternal_surname'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'image' => $imagePath,
            ]);

            DB::commit();
            return redirect()->route('voyager.people.index')->with([
                'message' => 'Registrado exitosamente',
                'alert-type' => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.people.index')->with([
                'message' => $th->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }


    public function update(Request $request, $id)
    {
        $this->custom_authorize('edit_people');

        $validated = $request->validate([
            'ci' => 'required|string|regex:/^[0-9]{7,10}$/|unique:people,ci,' . $id,
            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'maternal_surname' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:people,email,' . $id . ',id|max:255',
            'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
            'gender' => 'nullable|string',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $storageController = new StorageController();
            $person = Person::find($id);

            if (!$person) {
                throw new \Exception('Persona no encontrada.');
            }

            $person->ci = $validated['ci'];
            $person->birth_date = $validated['birth_date'];
            $person->gender = $validated['gender'];
            $person->first_name = $validated['first_name'];
            $person->middle_name = $validated['middle_name'];
            $person->paternal_surname = $validated['paternal_surname'];
            $person->maternal_surname = $validated['maternal_surname'];
            $person->email = $validated['email'];
            $person->phone = $validated['phone'];
            $person->address = $validated['address'];
            $person->status = $request->has('status') ? 1 : 0;

            if ($request->image) {
                $person->image = $storageController->store_image($request->image, 'people');
            }

            $person->save();

            DB::commit();
            return redirect()->route('voyager.people.index')->with([
                'message' => 'Actualizada exitosamente',
                'alert-type' => 'success'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.people.index')->with([
                'message' => $th->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}
