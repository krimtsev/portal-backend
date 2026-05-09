<?php
namespace App\Helpers\Pagination;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class Pagination
{
    public static function paginate(
        Builder $query,
        Request $request,
        array $searchColumns = [],
        array $sortable = [],
        array $filterable = [],
    ): array
    {
        [
            $sortBy,
            $sortOrder,
            $perPage,
            $search,
            $page,
            $filters
        ] = (new QueryParameters($request, $sortable))->all();

        // Поиск
        if (!empty($search) && !empty($searchColumns)) {
            self::applySearch($query, $search, $searchColumns);
        }

        // Фильтры
        if (!empty($filters) && is_array($filters)) {
            $filterableColumns = $filterable['columns'] ?? [];
            $filterableRelations = $filterable['relations'] ?? [];

            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                if (in_array($column, $filterableColumns)) {
                    self::applyFilter($query, $column, $value);
                } elseif (in_array($column, $filterableRelations)) {
                    self::applyRelationFilter($query, $column, $value);
                }
            }
        }

        // Сортировка
        if (in_array($sortBy, $sortable)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Пагинация
        $list = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'list' => $list->items(),
            'page' => self::formatResponse($list)
        ];
    }

    public static function formatResponse(LengthAwarePaginator $list): array
    {
        return [
            'currentPage' => $list->currentPage(),
            'lastPage'    => $list->lastPage(),
            'perPage'     => $list->perPage(),
            'total'       => $list->total(),
            'from'        => $list->firstItem() ?? 0,
            'to'          => $list->lastItem() ?? 0,
        ];
    }

    protected static function applyFilter(Builder $query, string $column, mixed $value): void
    {
        if (is_array($value)) {
            $query->where(function($q) use ($column, $value) {
                foreach ($value as $v) {
                    $q->orWhere($column, $v);
                }
            });
        } else {
            $query->where($column, $value);
        }
    }

    protected static function applyRelationFilter(Builder $query, string $relation, mixed $value): void
    {
        $query->whereHas($relation, function ($q) use ($relation, $value) {
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    if (!is_int($key)) {
                        $q->where($key, $val);
                    } else {
                        $q->where($val, true);
                    }
                }
            } else {
                $q->where($relation, $value);
            }
        });
    }

    protected static function applySearch(Builder $query, string $search, array $columns): void
    {
        $searchTerm = trim($search);
        $query->where(function ($q) use ($searchTerm, $columns) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $relColumn] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($q2) use ($relColumn, $searchTerm) {
                        $q2->where($relColumn, 'like', "%{$searchTerm}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$searchTerm}%");
                }
            }
        });
    }
}
