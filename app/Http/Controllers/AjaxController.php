<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function personList()
    {
        $q = request('q');

        $query = Person::where('deleted_at', null);

        if ($q) {
            $query->where('ci', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%")
                  ->orWhere('first_name', 'like', "%{$q}%")
                  ->orWhere('middle_name', 'like', "%{$q}%")
                  ->orWhere('paternal_surname', 'like', "%{$q}%")
                  ->orWhere('maternal_surname', 'like', "%{$q}%")
                  ->orWhere(function ($subQ) use ($q) {
                      $fullName = "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''))";
                      $subQ->whereRaw("{$fullName} like ?", ["%{$q}%"]);
                  })
                  ->orWhere(function ($subQ) use ($q) {
                      $fullName = "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, ''))";
                      $subQ->whereRaw("{$fullName} like ?", ["%{$q}%"]);
                  })
                  ->orWhere(function ($subQ) use ($q) {
                      $fullName = "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, ''))";
                      $subQ->whereRaw("{$fullName} like ?", ["%{$q}%"]);
                  });
        }

        $data = $query->get();

        return response()->json($data);
    }

    public function personStore(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'paternal_surname' => 'required|string|max:255',
            'ci' => 'required|string|unique:people',
            'email' => 'nullable|email|unique:people',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string',
            'birth_date' => 'nullable|date|before:today',
        ]);

        DB::beginTransaction();
        try {
            $person = Person::create($validated);
            DB::commit();
            return response()->json(['person' => $person], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
