<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Ticket\TicketCategory;
use App\Http\Requests\Ticket\TicketsCategoriesRequest;
use Illuminate\Http\Request;
use App\Http\Responses\JsonResponse;
use App\Helpers\Pagination\Pagination;


class TicketsCategoriesController extends Controller
{
    /**
     * Получить полный список категорий
     */
    public function list(): \Illuminate\Http\JsonResponse
    {
        $list = TicketCategory::select(
            'id',
            'title',
            'slug',
        )->orderBy('title')
        ->get();

        return JsonResponse::Send([
            'list' => $list
        ]);
    }

    /**
     * Получить список категорий с пагинацией
     */
    public function listPaginated(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = TicketCategory::select(
            'id',
            'title',
        );

        return JsonResponse::Send(Pagination::paginate(
            $query,
            $request,
            ['title'],
            ['id']
        ));
    }

    /**
     * Обновить категорию
     */
    public function update(TicketsCategoriesRequest $request, TicketCategory $category): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $category->update($data);

        return JsonResponse::Send([]);
    }

    /**
     * Получить информаицю категории по slug
     * Используется для получения id
     * id категории может отличаться у разных парнтеров
     */
    public function getCategoryBySlug(TicketCategory $category): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => $category->only(['id', 'title', 'slug'])
        ]);
    }
}
