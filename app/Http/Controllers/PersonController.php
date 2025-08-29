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

    public function list(){

        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;

        $data = Person::where(function($query) use ($search){
                            $query->OrWhereRaw($search ? "id = '$search'" : 1)
                            ->OrWhereRaw($search ? "ci like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "phone like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "first_name like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "middle_name like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "paternal_surname like '%$search%'" : 1)
                            ->OrWhereRaw($search ? "maternal_surname like '%$search%'" : 1)
                        // ->OrWhereRaw($search ? "CONCAT(first_name, ' ', middle_name, ' ', paternal_surname) like '%$search%'" : 1)
                            ->orWhere(function ($subQ) use ($search) {
                                $subQ->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, '')) like ?", ["%$search%"])
                                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$search%"])
                                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$search%"]);
                            });
                        })
                        ->where('deleted_at', NULL)->orderBy('id', 'DESC')->paginate($paginate);

        return view('administrations.people.list', compact('data'));
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_people');
        $request->validate([
            'image' => 'image|mimes:jpeg,jpg,png,bmp,webp'
        ]);
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


            $person->update();

            DB::commit();
            return redirect()->route('voyager.people.index')->with(['message' => 'Actualizada exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.people.index')->with(['message' => $th->getMessage(), 'alert-type' => 'error']);
        }
    }
}
