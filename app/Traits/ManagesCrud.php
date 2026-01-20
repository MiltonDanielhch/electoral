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

        $modelInstance = new $this->model;
        $primaryKey = $modelInstance->getKeyName();

        $query = ($this->model)::query();

        if (property_exists($this, 'with') && !empty($this->with)) {
            $query->with($this->with);
        }

        if ($search && method_exists($this, 'applySearch')) {
            $this->applySearch($query, $search);
        }

        $items = $query->orderBy($primaryKey, 'desc')->paginate($paginate);

        return view($this->listView, ['items' => $items]);
    }
}
