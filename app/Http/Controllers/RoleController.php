<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use TCG\Voyager\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // public function index()
    // {
    //     $this->custom_authorize('browse_roles');
    //     return view('administrations.people.browse');
    // }
    
    public function list()
    {
        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;

        $rol_id = Auth::user()->role->id;
        $page = request('page', 1);

        $cacheKey = "roles_list_{$rol_id}_{$search}_{$paginate}_{$page}";

        $data = Cache::remember($cacheKey, 300, function() use ($search, $paginate, $rol_id) {
            return Role::where(function($query) use ($search){
                        if ($search) {
                            if (is_numeric($search)) {
                                $query->where('id', $search);
                            } else {
                                $query->where('name', 'like', "%{$search}%")
                                       ->orWhere('display_name', 'like', "%{$search}%");
                            }
                        }
                    })
                    ->when($rol_id != 1, function ($query) {
                        return $query->where('id', '!=', 1);
                    })
                    ->orderBy('id', 'DESC')
                    ->paginate($paginate);
        });

        return view('vendor.voyager.roles.list', compact('data'));
    }
}
