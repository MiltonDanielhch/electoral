<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\User;
use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
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

    public function store(StorePersonRequest $request)
    {
        $validated = $request->validated();

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


    public function update(UpdatePersonRequest $request, $id)
    {
        $validated = $request->validated();

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
