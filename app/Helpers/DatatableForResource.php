<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Exports\DatatableExport;

class DatatableForResource
{
    // Original Query Builder Instance
    private $initialQuery;

    // Modified Query Builder Instance
    private $query;

    // Data Sort Column
    public $sortColumn;

    // Data Sort Order
    public $order;

    // Page Limit
    public $limit = 100;

    // Page Number
    public $page = 1;

    // Page Number
    public $filter;

    public $search;

    public $fullTextSearch;

    private $searchColumns = [];

    private $filters = [];

    private $rangeColumn;

    private $rangeStart;

    private $rangeEnd;

    public function __construct($query, array $config = [])
    {

        if (!$query instanceof EloquentBuilder && !$query instanceof QueryBuilder) {
            throw new \Exception(
                "Argument 1 passed to App\Classes\Datatable::make() must be an instance of
                Illuminate\Database\Eloquent\Builder or Illuminate\Database\Query\Builder",
                1
            );
        }

        $this->initialQuery = $query;

        $this->query = $query;

        $this->getQueryFilters();

        if (isset($config['search']) && (gettype($config['search']) == 'array' || is_callable($config['search']))) {
            $this->searchColumns = $config['search'];
        }

        if (isset($config['filters']) && gettype($config['filters']) == 'array') {
            $this->filters = $config['filters'];
        }

        if (isset($config['sort']) && gettype($config['sort']) == 'array') {
            $sort = $config['sort'];
            $this->sortColumn = isset($sort['column']) ? $sort['column'] : null;
            $this->order = isset($sort['order']) ? $sort['order'] : null;
        }

        if ($this->search && !is_null($this->search) && gettype($this->search) == 'string') {
            $this->searchData();
        }

        if ($this->filter && !is_null($this->filter) && gettype($this->filter) == 'string') {
            $this->filterData();
        }

        if (array_key_exists('rangeColumn', $config)) {
            $this->rangeColumn = $config['rangeColumn'];
            $this->rangeStart = request()->rangeStart;
            $this->rangeEnd = request()->rangeEnd;

            if ($this->rangeColumn && $this->rangeStart && $this->rangeEnd) {
                $this->setRange();
            }
        }
    }

    public static function make($query, string $resource, array $config = [])
    {
        if (!$query instanceof EloquentBuilder && !$query instanceof QueryBuilder) {
            throw new \Exception(
                "Argument 1 passed to App\Classes\Datatable::make() must be an instance of
                Illuminate\Database\Eloquent\Builder or Illuminate\Database\Query\Builder",
                1
            );
        }

      // Get Class Name
        $className = self::class;

      // Instanciate new Datatable Builder
        $datatable = new $className($query, $config);

        $totalCount = $datatable->countTotal();

        $chunk = $datatable->getData();

        $chunk = $resource::collection($chunk);

        $pageCount = $datatable->countPages();

        $chunkCount = count($chunk);

        $data['data'] = [
            'data' => $chunk,
        ];

        $data['metadata'] = [
            'total_count' => $totalCount,
            'chunk_count' => $chunkCount,
            'page_count' => $pageCount,
            'page' => $datatable->page,
            'limit' => $datatable->limit,
            'order' => $datatable->order,
        ];
        if (config('app.env') == 'local') {
            $data['metadata']['query'] = $datatable->getDataQuery();
        }

        return $data;
    }

    private function getQuery()
    {
        return (clone $this->query);
    }

    private function getInitialQuery()
    {
        return (clone $this->initialQuery);
    }

    private function countTotal()
    {
        return $this->getQuery()
        ->count();
    }

    private function countData()
    {
        return $this->getQuery()
        ->offset(($this->page - 1) * $this->limit)
        ->limit($this->limit)
        ->count();
    }

    private function getDataQuery()
    {
        $query = $this->getQuery();

        if ($this->fullTextSearch && $this->search && !is_null($this->search) && gettype($this->search) == 'string') {
            $query->offset(0)->limit(1);
        } else {
            $query->offset(($this->page - 1) * $this->limit)
            ->limit($this->limit);
        }

        if ($this->sortColumn !== false && $this->order !== false) {
            if ($this->order == "ASC" || $this->order == "asc") {
                $sorted = $query->oldest($this->sortColumn);
            } else {
                $sorted = $query->latest($this->sortColumn);
            }
        }

        return $query->toSql();
    }

    private function countPages()
    {
        return ceil($this->countTotal() / $this->limit);
    }

    private function getData()
    {
        $query = $this->getQuery();

        if ($this->fullTextSearch && $this->search && !is_null($this->search) && gettype($this->search) == 'string') {
            $query->offset(0)->limit(1);
        } else {
            $query->offset(($this->page - 1) * $this->limit)
            ->limit($this->limit);
        }

      // Handle Sorting
        if ($this->sortColumn !== false && $this->order !== false) {
            if ($this->order == "ASC" || $this->order == "asc") {
                $sorted = $query->oldest($this->sortColumn);
            } else {
                $sorted = $query->latest($this->sortColumn);
            }
        }

        return $query->get();
    }

    private function searchData()
    {
        $key = $this->search;

        if (is_null($key)) {
            return false;
        }

        $this->query = $this->query->where(function ($query) {
            $index = 0;

            if (is_callable($this->searchColumns)) {
                call_user_func_array($this->searchColumns, [$query, $this->search]);
            } else {
                foreach ($this->searchColumns as $column) {
                    $this->addSearchQuery($query, $column, $index === 0);
                    $index++;
                }
            }
        });
    }

    private function addSearchQuery($query, $column, $startOfQuery = false)
    {
        if (!$startOfQuery) {
            if ($this->fullTextSearch) {
                $query->orwhere($column, '=', $this->search);
            } else {
                $query->orwhere($column, 'like', "%" . $this->search . "%");
            }
        } else {
            if ($this->fullTextSearch) {
                $query->where($column, '=', $this->search);
            } else {
                $query->where($column, 'like', "%" . $this->search . "%");
            }
        }
    }

    private function filterData()
    {
        $filterName = $this->filter;

        $filter = isset($this->filters[$filterName]) ? $this->filters[$filterName] : null;

        if (is_null($filter)) {
            return false;
        }

        call_user_func_array($filter, [$this->query]);
    }

    public function setRange()
    {
        $start = date('Y-m-d', strtotime($this->rangeStart));
        $end = date('Y-m-d', strtotime($this->rangeEnd));
        $this->query = $this->query->where($this->rangeColumn, '>', $start)->where($this->rangeColumn, '<', $end);
    }

    private function getQueryFilters()
    {
        $this->order = request()->input('order') ?? null;

        $this->page = request()->input('page') ?? 1;

        $this->limit = request()->input('limit') ?? 10;

        $this->search = request()->input('search') ?? null;

        $this->fullTextSearch = request()->input('fullTextSearch') ?? false;

        $this->filter = request()->input('filter') ?? null;
    }
}
