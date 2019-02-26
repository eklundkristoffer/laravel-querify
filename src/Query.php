<?php

namespace Eklundkristoffer\Querify;

use DB;

abstract class Query
{
    protected $table;

    protected $pagination = false;

    protected $sortable = false;

    protected $filterable = false;

    protected $searchable = false;

    protected $searchableFields = [];

    public function __construct()
    {
        $this->query = DB::table($this->table);
        $this->request = request();
    }

    public function get()
    {
        if (method_exists($this, 'inject')) {
            $this->injectRouteParameters();
        }

        if ($this->sortable && $this->request->has('sort')) {
            $this->sortQuery();
        }

        $this->query = $this->query();

        if ($this->filterable && $this->request->filled('filters')) {
            $this->filterQuery();
        }

        if ($this->searchable && $this->request->filled('searchString')) {
            $this->searchQuery($this->request->get('searchString'));
        }

        return $this->pagination ?
            $this->query->paginate($this->request->get('limit', 10)) :
            $this->query->get();
    }

    public function injectRouteParameters()
    {
        foreach ($this->inject() as $key => $value) {
            $this->{$key} = resolve($value)->find(
                $this->request->route()->parameters[$key]
            );
        }
    }

    public function sortQuery()
    {
        $sorts = explode(',', $this->request->sort);

        foreach ($sorts as $sort) {
            $column = str_replace('-', '', $sort);

            $this->query = $this->query->orderByRaw($column.' '.((substr($sort, 0, 1) === '-') ? 'asc' : 'desc'));
        }
    }

    public function searchQuery($searchString)
    {
        $this->query = $this->query->whereLike($this->searchableFields, $searchString);
    }

    public function filterQuery()
    {
        foreach ($this->request->filters as $column => $values) {
            $this->query = $this->query->whereIn($column, $values);
        }
    }

    abstract function query();
}
