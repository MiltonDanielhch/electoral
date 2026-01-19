# Ejemplo CRUD Completo - Empleado

Este es el mejor ejemplo de CRUD completo del sistema. Sigue todas las mejores prácticas de Laravel incluyendo:

- Trait `ManagesCrud` para reutilizar lógica de listado AJAX
- Form Requests para validación separada (Store/Update)
- Policies para autorización basada en permisos
- SoftDeletes para eliminación suave
- Relaciones entre modelos (empresa, departamento, usuario)
- Manejo de archivos (foto_perfil)
- AJAX para listado dinámico
- Paginación con búsqueda debounced

---

## 1. TRAIT: app/Traits/ManagesCrud.php

Este trait centraliza la lógica común de listado con AJAX y paginación:

```php
<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait ManagesCrud
{
    public function index()
    {
        $this->authorize('viewAny', $this->model);
        return view($this->browseView);
    }

    public function list(Request $request)
    {
        $this->authorize('viewAny', $this->model);

        $search = $request->get('search', '');
        $paginate = $request->get('paginate', 10);

        $query = ($this->model)::query();

        if (property_exists($this, 'with') && !empty($this->with)) {
            $query->with($this->with);
        }

        if ($search && method_exists($this, 'applySearch')) {
            $this->applySearch($query, $search);
        }

        $items = $query->orderBy('id', 'desc')->paginate($paginate);

        return view($this->listView, ['items' => $items]);
    }
}
```

---

## 2. MODELO: app/Models/Empleado.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'empresa_id',
        'departamento_id',
        'codigo_empleado',
        'dni',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'genero',
        'email',
        'telefono',
        'direccion',
        'fecha_contratacion',
        'tipo_contrato',
        'estado',
        'foto_perfil',
        'user_id',
        'creado_por',
    ];

    protected $casts = [
        'fecha_nacimiento'   => 'date',
        'fecha_contratacion' => 'date',
    ];

    public function getFullNameAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function creador()
    {
        return $this->belongsTo(\App\Models\User::class, 'creado_por');
    }

    public function huellas()
    {
        return $this->hasMany(Huella::class);
    }

    public function rostros()
    {
        return $this->hasMany(Rostro::class);
    }

    public function registrosAsistencia()
    {
        return $this->hasMany(RegistroAsistencia::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }

    public function asignacionesHorario()
    {
        return $this->hasMany(AsignacionHorario::class);
    }

    public function dispositivos()
    {
        return $this->belongsToMany(Dispositivo::class, 'dispositivo_empleado')
                    ->withPivot('zk_user_id', 'privilegio')
                    ->withTimestamps();
    }
}
```

---

## 3. CONTROLADOR: app/Http/Controllers/EmpleadoController.php

```php
<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Empresa;
use App\Models\Departamento;
use App\Http\Requests\StoreEmpleadoRequest;
use App\Http\Requests\UpdateEmpleadoRequest;
use App\Traits\ManagesCrud;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmpleadoController extends Controller
{
    use ManagesCrud;

    protected $model = Empleado::class;
    protected $browseView = 'admin.empleados.browse';
    protected $listView = 'admin.empleados.list';
    protected $with = ['empresa', 'departamento'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function applySearch(Builder $query, string $search): Builder
    {
        return $query->where('nombres', 'like', "%$search%")
            ->orWhere('apellidos', 'like', "%$search%")
            ->orWhere('dni', 'like', "%$search%")
            ->orWhere('codigo_empleado', 'like', "%$search%");
    }

    public function create()
    {
        $this->authorize('create', Empleado::class);
        $empresas = Empresa::where('estado', 'activo')->get();
        $departamentos = Departamento::where('estado', 'activo')->get();
        return view('admin.empleados.edit-add', [
            'empleado' => new Empleado(),
            'empresas' => $empresas,
            'departamentos' => $departamentos,
        ]);
    }

    public function store(StoreEmpleadoRequest $request)
    {
        $this->authorize('create', Empleado::class);
        $data = $request->validated();

        if ($request->hasFile('foto_perfil')) {
            $data['foto_perfil'] = $request->file('foto_perfil')->store('empleados/fotos', 'public');
        }

        $data['creado_por'] = auth()->id();
        Empleado::create($data);

        return redirect()->route('admin.empleados.index')
            ->with(['message' => 'Empleado creado exitosamente.', 'alert-type' => 'success']);
    }

    public function edit(Empleado $empleado)
    {
        $this->authorize('update', $empleado);
        $empresas = Empresa::where('estado', 'activo')->get();
        $departamentos = Departamento::where('estado', 'activo')->get();
        return view('admin.empleados.edit-add', compact('empleado', 'empresas', 'departamentos'));
    }

    public function update(UpdateEmpleadoRequest $request, Empleado $empleado)
    {
        $this->authorize('update', $empleado);
        $data = $request->validated();

        if ($request->hasFile('foto_perfil')) {
            if ($empleado->foto_perfil) {
                Storage::disk('public')->delete($empleado->foto_perfil);
            }
            $data['foto_perfil'] = $request->file('foto_perfil')->store('empleados/fotos', 'public');
        }

        $empleado->update($data);

        return redirect()->route('admin.empleados.index')
            ->with(['message' => 'Empleado actualizado exitosamente.', 'alert-type' => 'success']);
    }

    public function show(Empleado $empleado)
    {
        $this->authorize('view', $empleado);
        $empleado->load(['empresa', 'departamento', 'creador']);
        return view('admin.empleados.read', compact('empleado'));
    }

    public function destroy(Empleado $empleado)
    {
        $this->authorize('delete', $empleado);

        // Verificar dependencias antes de eliminar
        if ($empleado->registrosAsistencia()->exists()) {
            return back()->with(['message' => 'No se puede eliminar: tiene registros de asistencia asociados.', 'alert-type' => 'error']);
        }

        try {
            if ($empleado->foto_perfil) {
                Storage::disk('public')->delete($empleado->foto_perfil);
            }
            $empleado->delete();
            return redirect()->route('admin.empleados.index')
                ->with(['message' => 'Empleado eliminado exitosamente.', 'alert-type' => 'success']);
        } catch (\Exception $e) {
            Log::error("Error al eliminar empleado {$empleado->id}: " . $e->getMessage());
            return redirect()->route('admin.empleados.index')
                ->with(['message' => 'Error al eliminar el empleado.', 'alert-type' => 'error']);
        }
    }

    // Opcional: Búsqueda AJAX para select2/autocomplete
    public function ajaxSearch(Request $request)
    {
        $term = $request->get('q', '');

        $empleados = Empleado::where(function($query) use ($term) {
                $query->where('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidos', 'like', "%{$term}%")
                    ->orWhere('dni', 'like', "%{$term}%")
                    ->orWhere('codigo_empleado', 'like', "%{$term}%");
            })
            ->where('estado', 'activo')
            ->with('empresa')
            ->limit(20)
            ->get();

        $formatted = $empleados->map(function($empleado) {
            return [
                'id' => $empleado->id,
                'text' => $empleado->full_name,
                'dni' => $empleado->dni,
                'codigo' => $empleado->codigo_empleado,
                'empresa' => $empleado->empresa->nombre_empresa ?? 'N/A'
            ];
        });

        return response()->json(['results' => $formatted]);
    }
}
```

---

## 4. FORM REQUEST (STORE): app/Http/Requests/StoreEmpleadoRequest.php

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmpleadoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'empresa_id' => 'required|exists:empresas,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'codigo_empleado' => 'required|string|max:50|unique:empleados',
            'dni' => 'required|string|max:20|unique:empleados',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|string',
            'email' => 'nullable|email|unique:empleados',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'fecha_contratacion' => 'required|date',
            'tipo_contrato' => ['required', 'string', 'max:50', Rule::in(['indefinido', 'plazo_fijo', 'servicios'])],
            'estado' => 'required|string',
            'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'empresa_id.required' => 'La empresa es obligatoria.',
            'codigo_empleado.required' => 'El código de empleado es obligatorio.',
            'codigo_empleado.unique' => 'Este código de empleado ya está en uso.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.unique' => 'Este DNI ya está en uso.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'fecha_contratacion.required' => 'La fecha de contratación es obligatoria.',
            'tipo_contrato.required' => 'El tipo de contrato es obligatorio.',
            'tipo_contrato.in' => 'El tipo de contrato seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
```

---

## 5. FORM REQUEST (UPDATE): app/Http/Requests/UpdateEmpleadoRequest.php

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpleadoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $empleadoId = $this->route('empleado')->id;

        return [
            'empresa_id' => 'required|exists:empresas,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
            'codigo_empleado' => ['required', 'string', 'max:50', Rule::unique('empleados')->ignore($empleadoId)],
            'dni' => ['required', 'string', 'max:20', Rule::unique('empleados')->ignore($empleadoId)],
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|string',
            'email' => ['nullable', 'email', Rule::unique('empleados')->ignore($empleadoId)],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'fecha_contratacion' => 'required|date',
            'tipo_contrato' => ['required', 'string', 'max:50', Rule::in(['indefinido', 'plazo_fijo', 'servicios'])],
            'estado' => 'required|string',
            'foto_perfil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'empresa_id.required' => 'La empresa es obligatoria.',
            'codigo_empleado.required' => 'El código de empleado es obligatorio.',
            'codigo_empleado.unique' => 'Este código de empleado ya está en uso.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.unique' => 'Este DNI ya está en uso.',
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'fecha_contratacion.required' => 'La fecha de contratación es obligatoria.',
            'tipo_contrato.required' => 'El tipo de contrato es obligatorio.',
            'tipo_contrato.in' => 'El tipo de contrato seleccionado no es válido.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
```

---

## 6. RUTAS: routes/web.php

```php
// Empleados
Route::get('empleados/ajax/list', [EmpleadoController::class, 'list'])->name('admin.empleados.ajax.list');
Route::get('empleados/ajax/search', [EmpleadoController::class, 'ajaxSearch'])->name('admin.empleados.ajax.search');
Route::resource('empleados', EmpleadoController::class)->names('admin.empleados');
```

---

## 7. VISTAS

### resources/views/admin/empleados/browse.blade.php

```php
@extends('voyager::master')

@section('page_title', 'Empleados')

@section('page_header')
    <div class="container-fluid">
        @include('voyager::alerts')

        @if(session('message'))
            <div class="alert alert-{{ session('alert-type', 'info') }} alert-dismissible auto-dismiss">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                {{ session('message') }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" style="margin-bottom: 0;">
                    <div class="panel-body" style="padding: 0;">
                        <div class="col-md-8" style="padding: 0;">
                                <h1 class="page-title">
                                <i class="voyager-people"></i> Empleados
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px;">
                            @can('create', App\Models\Empleado::class)
                                <a href="{{ route('admin.empleados.create') }}" class="btn btn-success">
                                    <i class="voyager-plus"></i> Nuevo Empleado
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-9" style="margin-bottom: 0">
                                <div class="dataTables_length" id="dataTable">
                                    <label>Mostrar
                                        <select id="select-paginate" class="form-control input-sm">
                                            <option value="10" selected>10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select> registros
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-3" style="margin-bottom: 0;">
                                <input type="text" id="search" class="form-control" placeholder="Buscar...">
                                <br>
                            </div>
                        </div>
                        <div class="row" id="list-container" style="min-height: 120px">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .loading-icon {
        animation: spin 1.5s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .voyager-spin {
        animation: spin 1.5s linear infinite;
    }
</style>
@endsection

{{-- Modal eliminar --}}
<div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><i class="voyager-trash"></i> ¿Desea eliminar este empleado?</h4>
            </div>
            <div class="modal-footer">
                <form action="#" id="delete_form" method="POST">
                    @method('DELETE') @csrf
                    <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                </form>
                <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@push('javascript')
    @include('admin.partials.list-browse-script', ['listUrl' => route('admin.empleados.ajax.list')])
@endpush
```

### resources/views/admin/empleados/list.blade.php

```php
<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Empleado</th>
                <th>DNI / Código</th>
                <th>Empresa</th>
                <th>Departamento</th>
                <th>Estado</th>
                <th class="actions text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $empleado)
                <tr>
                    <td>{{ $empleado->id }}</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <img src="{{ $empleado->foto_perfil ? Storage::url($empleado->foto_perfil) : asset('img/default-avatar.png') }}" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
                            <div>
                                <strong>{{ $empleado->full_name }}</strong><br>
                                <small>{{ $empleado->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        DNI: {{ $empleado->dni }}<br>
                        <small>Código: {{ $empleado->codigo_empleado }}</small>
                    </td>
                    <td>{{ $empleado->empresa->nombre_empresa ?? 'N/A' }}</td>
                    <td>{{ $empleado->departamento->nombre_departamento ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-{{ $empleado->estado == 'activo' ? 'success' : 'danger' }}">{{ ucfirst($empleado->estado) }}</span>
                    </td>
                    <td class="no-sort no-click bread-actions text-right">
                        <a href="{{ route('admin.empleados.edit', $empleado->id) }}" title="Editar" class="btn btn-sm btn-primary edit">
                            <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                        </a>
                        <button title="Borrar" class="btn btn-sm btn-danger delete" data-id="{{ $empleado->id }}" data-toggle="modal" data-target="#delete_modal" onclick="deleteItem('{{ route('admin.empleados.destroy', $empleado->id) }}', '{{ $empleado->full_name }}')">
                            <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No se encontraron empleados.</td>
                </tr>
            @endforelse
        </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4 text-muted">
        @if($items->count() > 0)
            <p class="text-muted">Mostrando del {{ $items->firstItem() }} al {{ $items->lastItem() }} de {{ $items->total() }} registros.</p>
        @endif
    </div>
    <div class="col-md-8 text-right">
        <nav class="text-right">
            {{ $items->links() }}
        </nav>
    </div>
</div>
```

### resources/views/admin/empleados/edit-add.blade.php

```php
@extends('voyager::master')

@section('page_title', ($empleado->exists ? 'Editar' : 'Agregar') . ' Empleado')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-people"></i>
            {{ $empleado->exists ? 'Editar Empleado' : 'Agregar Empleado' }}
        </h1>
        <a href="{{ route('admin.empleados.index') }}" class="btn btn-warning btn-add-new">
            <i class="voyager-list"></i> <span>Volver a la lista</span>
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <form method="POST" action="{{ $empleado->exists ? route('admin.empleados.update', $empleado->id) : route('admin.empleados.store') }}" enctype="multipart/form-data">
            @csrf
            @if($empleado->exists)
                @method('PUT')
            @endif
            <div class="panel panel-bordered">
                <div class="panel-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="nombres">Nombres <span class="text-danger">*</span></label>
                            <input type="text" name="nombres" id="nombres" class="form-control" value="{{ old('nombres', $empleado->nombres) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="apellidos">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" name="apellidos" id="apellidos" class="form-control" value="{{ old('apellidos', $empleado->apellidos) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="dni">DNI <span class="text-danger">*</span></label>
                            <input type="text" name="dni" id="dni" class="form-control" value="{{ old('dni', $empleado->dni) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="codigo_empleado">Código de Empleado <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_empleado" id="codigo_empleado" class="form-control" value="{{ old('codigo_empleado', $empleado->codigo_empleado) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $empleado->email) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="telefono">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono', $empleado->telefono) }}">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="empresa_id">Empresa <span class="text-danger">*</span></label>
                            <select name="empresa_id" id="empresa_id" class="form-control select2" required>
                                <option value="">-- Seleccione una empresa --</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" @if(old('empresa_id', $empleado->empresa_id) == $empresa->id) selected @endif>{{ $empresa->nombre_empresa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="departamento_id">Departamento</label>
                            <select name="departamento_id" id="departamento_id" class="form-control select2">
                                <option value="">-- Sin departamento --</option>
                                @foreach($departamentos as $depto)
                                    <option value="{{ $depto->id }}" @if(old('departamento_id', $empleado->departamento_id) == $depto->id) selected @endif>{{ $depto->nombre_departamento }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="fecha_contratacion">Fecha de Contratación <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_contratacion" id="fecha_contratacion" class="form-control" value="{{ old('fecha_contratacion', optional($empleado->fecha_contratacion)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="tipo_contrato">Tipo de Contrato <span class="text-danger">*</span></label>
                            <select name="tipo_contrato" id="tipo_contrato" class="form-control select2" required>
                                <option value="indefinido" @if(old('tipo_contrato', $empleado->tipo_contrato) == 'indefinido') selected @endif>Indefinido</option>
                                <option value="plazo_fijo" @if(old('tipo_contrato', $empleado->tipo_contrato) == 'plazo_fijo') selected @endif>Plazo Fijo</option>
                                <option value="servicios" @if(old('tipo_contrato', $empleado->tipo_contrato) == 'servicios') selected @endif>Servicios</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="estado">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-control select2" required>
                                <option value="activo" @if(old('estado', $empleado->estado, 'activo') == 'activo') selected @endif>Activo</option>
                                <option value="inactivo" @if(old('estado', $empleado->estado) == 'inactivo') selected @endif>Inactivo</option>
                            </select>
                        </div>
                    </div>
                     <div class="form-group">
                        <label for="foto_perfil">Foto de Perfil</label>
                        @if($empleado->foto_perfil)
                            <img src="{{ Storage::url($empleado->foto_perfil) }}" style="width: 100px; display: block; margin-bottom: 10px;">
                        @endif
                        <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> {{ $empleado->exists ? 'Actualizar Empleado' : 'Guardar Empleado' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('javascript')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2();
            }
        });
    </script>
@stop
```

---

### resources/views/admin/empleados/read.blade.php

```php
@extends('voyager::master')

@section('page_title', 'Ver Empleado')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Ver Empleado: {{ $empleado->full_name }}
            </h3>
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <img src="{{ $empleado->foto_perfil ? Storage::url($empleado->foto_perfil) : asset('img/default-avatar.png') }}"
                         style="width: 150px; height: 150px; border-radius: 50%; border: 3px solid #ddd; margin-bottom: 20px;"
                         alt="Foto de Perfil">
                    <h4>{{ $empleado->full_name }}</h4>
                    <p>{{ $empleado->email ?? 'Sin email' }}</p>
                    <span class="label label-{{ $empleado->estado == 'activo' ? 'success' : 'danger' }}">
                        {{ ucfirst($empleado->estado) }}
                    </span>
                </div>
                <div class="col-md-8">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 200px;">ID</th>
                                <td>{{ $empleado->id }}</td>
                            </tr>
                            <tr>
                                <th>DNI</th>
                                <td>{{ $empleado->dni }}</td>
                            </tr>
                            <tr>
                                <th>Código de Empleado</th>
                                <td>{{ $empleado->codigo_empleado }}</td>
                            </tr>
                             <tr>
                                <th>Teléfono</th>
                                <td>{{ $empleado->telefono ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Dirección</th>
                                <td>{{ $empleado->direccion ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Nacimiento</th>
                                <td>{{ $empleado->fecha_nacimiento ? $empleado->fecha_nacimiento->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Género</th>
                                <td>
                                    @if($empleado->genero == 'M')
                                        Masculino
                                    @elseif($empleado->genero == 'F')
                                        Femenino
                                    @else
                                        {{ $empleado->genero ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">Información Laboral</h5>
                    <table class="table table-bordered">
                         <tbody>
                            <tr>
                                <th style="width: 200px;">Empresa</th>
                                <td>{{ optional($empleado->empresa)->nombre_empresa ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Departamento</th>
                                <td>{{ optional($empleado->departamento)->nombre_departamento ?? 'Sin departamento' }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Contratación</th>
                                <td>{{ $empleado->fecha_contratacion->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Tipo de Contrato</th>
                                <td>{{ ucfirst(str_replace('_', ' ', $empleado->tipo_contrato)) }}</td>
                            </tr>
                        </tbody>
                    </table>

                     <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">Metadatos</h5>
                     <table class="table table-bordered">
                         <tbody>
                            <tr>
                                <th style="width: 200px;">Creado por</th>
                                <td>{{ optional($empleado->creador)->name ?? 'Sistema' }}</td>
                            </tr>
                            <tr>
                                <th>Creado</th>
                                <td>{{ optional($empleado->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Última Actualización</th>
                                <td>{{ optional($empleado->updated_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.empleados.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>

            @can('update', $empleado)
                <a href="{{ route('admin.empleados.edit', $empleado) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
```

---

## 8. SCRIPT PARCIAL PARA AJAX: resources/views/admin/partials/list-browse-script.blade.php

Este script reutilizable maneja toda la lógica de AJAX para el listado dinámico:

```php
@push('javascript')
<script>
    let countPage = 10;
    const listUrl = '{{ $listUrl }}';

    $(document).ready(function () {
        list();

        let searchTimeout;
        $('#search').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => list(1), 400);
        });

        $('#search').on('keyup', function (e) {
            if (e.keyCode === 13) {
                clearTimeout(searchTimeout);
                list(1);
            }
        });

        $('#select-paginate').on('change', function() {
            countPage = $(this).val();
            list(1);
        });

        $('#list-container').on('click', '.pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            list(page);
        });
    });

    function deleteItem(url, itemName) {
        $('#delete_form').attr('action', url);
        $('#delete_modal .modal-title').html(`<i class="voyager-trash"></i> ¿Estás seguro de que quieres eliminar "${itemName}"?`);
    }

    function list(page = 1) {
        const search = $('#search').val()?.trim() || '';

        let urlParams = new URLSearchParams({
            search: search,
            paginate: countPage,
            page: page
        });

        $('#list-container').html(`
            <div class="text-center" style="padding: 40px">
                <i class="voyager-refresh voyager-2x voyager-spin"></i><br>Cargando...
            </div>
        `);

        $.ajax({
            url: `${listUrl}?${urlParams.toString()}`,
            type: 'GET',
            success: response => $('#list-container').html(response),
            error: (xhr) => {
                console.error('Error al cargar la lista:', xhr);
                $('#list-container').html(`<div class="alert alert-danger text-center">Error al cargar los datos. Por favor, intenta de nuevo.</div>`);
            }
        });
    }
</script>
@endpush
```

---

## 9. MIGRACIÓN: database/migrations/XXXX_XX_XX_XXXXXX_create_empleados_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->unsignedBigInteger('departamento_id');
            $table->string('codigo_empleado', 50);
            $table->string('dni', 20);
            $table->string('nombres');
            $table->string('apellidos');
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->date('fecha_contratacion');
            $table->enum('tipo_contrato', ['indefinido', 'plazo_fijo', 'servicios']);
            $table->enum('estado', ['activo', 'inactivo', 'vacaciones', 'licencia'])->default('activo');
            $table->string('foto_perfil')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo_empleado']);
            $table->unique(['empresa_id', 'dni']);

            $table->index(['empresa_id', 'estado']);
            $table->index(['departamento_id', 'estado']);
            $table->index(['fecha_contratacion']);
            $table->index(['estado', 'fecha_contratacion']);
            $table->index(['nombres', 'apellidos']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
```

---

## 10. POLICY: app/Policies/EmpleadoPolicy.php

```php
<?php

namespace App\Policies;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmpleadoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('browse_empleados');
    }

    public function view(User $user, Empleado $empleado)
    {
        return $user->hasPermission('read_empleados');
    }

    public function create(User $user)
    {
        return $user->hasPermission('add_empleados');
    }

    public function update(User $user, Empleado $empleado)
    {
        return $user->hasPermission('edit_empleados');
    }

    public function delete(User $user, Empleado $empleado)
    {
        return $user->hasPermission('delete_empleados');
    }
}
```

---

## PATRONES A SEGUIR PARA NUEVOS CRUDs:

### 1. Usar el Trait `ManagesCrud`
En el controlador, define estas propiedades y usa el trait:
- `protected $model` = Nombre del modelo
- `protected $browseView` = Vista de la página principal
- `protected $listView` = Vista de la tabla AJAX
- `protected $with` = Array de relaciones a cargar
- Implementa `applySearch()` para búsqueda personalizada

### 2. Controlador
- Constructor con `middleware('auth')`
- Usar `use ManagesCrud` para index() y list()
- Implementar: show(), create(), store(), edit(), update(), destroy()
- Opcional: ajaxSearch() para búsquedas select2/autocomplete
- Usar `authorize()` en cada método
- Verificar dependencias antes de eliminar (`if ($model->relacion()->exists())`)
- Manejo de errores con try-catch y Log
- Manejo de archivos con Storage
- En show(), usar `load()` para eager loading de relaciones

### 3. Form Requests
- Separar en StoreXxxRequest y UpdateXxxRequest
- Usar `Rule::unique()->ignore($id)` para updates
- Incluir messages() personalizados
- Manejar upload de archivos

### 4. Modelo
- Usar `SoftDeletes`
- Definir `$fillable` y `$casts`
- Agregar accesors cuando sea necesario
- Definir todas las relaciones (belongsTo, hasMany, belongsToMany)

### 5. Vistas
- **browse.blade.php**: Página principal con controles de búsqueda y paginación, modal de eliminación
- **list.blade.php**: Tabla que se carga vía AJAX, con paginación
- **read.blade.php**: Vista de detalle con foto del perfil, información personal, laboral y metadatos
- **edit-add.blade.php**: Formulario único para create/edit
- Incluir `@include('admin.partials.list-browse-script')` para la lógica AJAX

### 6. Rutas
- Usar `Route::resource()` con nombres
- Agregar ruta específica para `ajax/list`
- Agrupar bajo `admin.` prefix

### 7. Policy
- Implementar viewAny, view, create, update, delete
- Usar `$user->hasPermission('browse_xxx')` para viewAny
- Usar `$user->hasPermission('read_xxx')` para view
- Usar `$user->hasPermission('add_xxx')` para create
- Usar `$user->hasPermission('edit_xxx')` para update
- Usar `$user->hasPermission('delete_xxx')` para delete
- Registrar en `AuthServiceProvider`

### 8. Validación
- Campos obligatorios marcados con `required`
- Reglas de unicidad para identifiers
- Validación de relaciones (`exists:tabla,id`)
- Uploads: `image|mimes:jpeg,png,jpg,gif|max:2048`
