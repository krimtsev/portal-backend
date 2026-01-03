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
    public function all(): \Illuminate\Http\JsonResponse
    {
        $list = TicketCategory::select(
            'id',
            'title',
            'slug',
        )->get();

        return JsonResponse::Send([
            'list' => $list
        ]);
    }

    public function list(Request $request): \Illuminate\Http\JsonResponse
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

    public function update(TicketsCategoriesRequest $request, TicketCategory $category): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $category->update($data);

        return JsonResponse::Send([]);
    }

    public function getCategoryBySlug(TicketCategory $category): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'data' => $category->only(['id', 'title', 'slug'])
        ]);
    }
}
