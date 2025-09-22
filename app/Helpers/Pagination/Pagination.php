<?php
namespace App\Helpers\Pagination;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Pagination
{
    public static function paginate(
        Builder $query,
        Request $request,
        array $searchColumns = [],
        array $sortable = [],
    ): array
    {
        $sortBy    = $request->query('sortBy', $sortable[0] ?? 'id');
        $sortOrder = $request->query('sortOrder', 'asc');
        $perPage   = (int) $request->query('perPage', 50);
        $search    = $request->query('search');
        $page      = (int) $request->query('page', 1);

        // Поиск
        if (!empty($search) && !empty($searchColumns)) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        // Сортировка
        if (in_array($sortBy, $sortable)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Пагинация
        $list = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'list' => $list->items(),
            'page' => [
                'currentPage' => $list->currentPage(),
                'lastPage'    => $list->lastPage(),
                'perPage'     => $list->perPage(),
                'total'       => $list->total(),
                'from'        => $list->firstItem() ?? 0,
                'to'          => $list->lastItem() ?? 0,
            ]
        ];
    }
}
