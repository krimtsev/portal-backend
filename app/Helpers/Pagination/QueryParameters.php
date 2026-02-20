<?php

namespace App\Helpers\Pagination;

use Illuminate\Http\Request;

class QueryParameters
{
    public string $sortBy;
    public string $sortOrder;
    public int $perPage;
    public ?string $search;
    public int $page;
    public array $filters;

    public function __construct(Request $request, array $sortable = [])
    {
        $this->sortBy    = $request->input('sortBy', $sortable[0] ?? 'id');
        $this->sortOrder = strtolower($request->input('sortOrder')) === 'desc' ? 'desc' : 'asc';
        $this->perPage   = (int) $request->input('perPage', 50);
        $this->search    = $request->input('search');
        $this->page      = (int) $request->input('page', 1);
        $this->filters   = $request->input('filters', []);
    }

    public function all(): array
    {
        return [
            $this->sortBy,
            $this->sortOrder,
            $this->perPage,
            $this->search,
            $this->page,
            $this->filters,
        ];
    }
}
