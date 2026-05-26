<?php

namespace App\Helpers\Pagination;

use App\Enums\Filter\FilterType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Pagination
{
    public static function paginate(
        Builder $query,
        Request $request,
        array $searchColumns = [],
        array $sortable = [],
        array $filterable = [],
    ): array {
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
            foreach ($filters as $column => $value) {
                if (is_array($value)) {
                    $value = array_filter($value, fn($item) => $item !== null && $item !== '');
                }

                if ($value === null || $value === '' || $value === []) {
                    continue;
                }

                if (in_array($column, $filterable)) {
                    self::applyFilter($query, $column, $value);
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
            'page' => self::formatResponse($list),
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
        $isArray = is_array($value);

        $hasWithoutData = $isArray
            ? in_array(FilterType::WITHOUT_DATA->value, $value, true)
            : $value === FilterType::WITHOUT_DATA->value;

        $value = $isArray ? array_diff($value, [FilterType::WITHOUT_DATA->value]) : $value;

        $query->where(function ($q) use ($column, $value, $isArray, $hasWithoutData) {
            if ($isArray) {
                if (!empty($value)) {
                    $q->whereIn($column, $value);
                }
            } else {
                if (!$hasWithoutData) {
                    $q->where($column, $value);
                }
            }

            if ($hasWithoutData) {
                $q->orWhereNull($column);
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
