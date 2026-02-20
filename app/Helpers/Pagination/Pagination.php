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
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    // Проверяем, связанная ли это модель (формат: relation.column)
                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);
                        $q->orWhereHas($relation, function ($q2) use ($relColumn, $search) {
                            $q2->where($relColumn, 'like', "%{$search}%");
                        });
                    } else {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        // Фильтры
        if (!empty($filters) && is_array($filters)) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;
                if (!in_array($column, $filterable)) continue;

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
}
