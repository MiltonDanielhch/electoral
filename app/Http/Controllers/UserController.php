<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // public function index()
    // {
    //     $this->custom_authorize('browse_users');
    //     return User::all();

    // return view('vendor.voyager.users.browse');
    // }


    public function list()
    {
        // $this->custom_authorize('browse_users');
        $rol_id = Auth::user()->role->id;

        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;

        $data = User::with(['person'])
            ->where(function($query) use ($search){
                if ($search) {
                    if (is_numeric($search)) {
                        $query->where('id', $search);
                    } else {
                        $query->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                    }
                }
            })
            ->when($rol_id != 1, function ($query) {
                return $query->where('role_id', '!=', 1);
            })
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('vendor.voyager.users.list', compact('data'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'person_id' => 'required|exists:people,id,deleted_at,NULL,status,1',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            $person = Person::where('deleted_at', null)
                            ->where('status', 1)
                            ->where('id', $validated['person_id'])
                            ->first();

            if (!$person) {
                throw new \Exception('La persona seleccionada no existe o no está activa.');
            }

            User::create([
                'person_id' => $validated['person_id'],
                'name' => $person->first_name,
                'role_id' => $validated['role_id'],
                'email' => $validated['email'],
                'avatar' => 'users/default.png',
                'password' => bcrypt($validated['password']),
            ]);

            DB::commit();
            return redirect()->route('voyager.users.index')->with([
                'message' => 'Registrado exitosamente.',
                'alert-type' => 'success'
            ]);

        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.users.index')->with([
                'message' => $th->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $id)->first();
            $user->update([
                'status'=> $request->status?1:0,
            ]);

            if($request->role_id)
            {
                $user->update([
                    'role_id' => $request->role_id,
                ]);
            }
            if($request->password)
            {
                $user->update([
                    'password' => bcrypt($request->password)
                ]);
            }
            DB::commit();
            return redirect()->route('voyager.users.index')->with(['message' => 'Actualizado exitosamente.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->route('voyager.users.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $id)->where('deleted_at', null)->first();
            $user->delete();
            DB::commit();
            return redirect()->route('voyager.users.index')->with(['message' => 'Eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('voyager.users.index')->with(['message' => 'Ocurrió un error.', 'alert-type' => 'error']);
        }
    }
}
