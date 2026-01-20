<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list()
    {
        $rol_id = Auth::user()->role->id;

        $search = request('search');
        $paginate = request('paginate', 10);

        $query = User::with(['person:id,first_name,paternal_surname,maternal_surname,image'])
            ->whereNull('deleted_at');

        if ($search) {
            if (is_numeric($search)) {
                $query->where('id', $search);
            } else {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }
        }

        if ($rol_id != 1) {
            $query->where('role_id', '!=', 1);
        }

        $data = $query->orderBy('id', 'DESC')->paginate($paginate);

        return view('vendor.voyager.users.list', compact('data'));
    }

    public function store(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $person = Person::where('deleted_at', null)
                            ->where('status', 1)
                            ->where('id', $request->person_id)
                            ->first();

            if (!$person) {
                throw new \Exception('La persona seleccionada no existe o no está activa.');
            }

            User::create([
                'person_id' => $request->person_id,
                'name' => $person->first_name,
                'role_id' => $request->role_id,
                'email' => $request->email,
                'avatar' => 'users/default.png',
                'password' => bcrypt($request->password),
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

    public function update(UpdateUserRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $id)->first();
            $user->update([
                'status'=> $request->status?1:0,
            ]);

            if($request->has('role_id'))
            {
                $user->update([
                    'role_id' => $request->role_id,
                ]);
            }
            if($request->has('password'))
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
