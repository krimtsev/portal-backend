<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserListResource;
use App\Http\Responses\JsonResponse;
use App\Models\User\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = User::select(
            'id',
            'login',
            'role',
            'partner_id',
            'disabled',
            'last_activity'
        )->with([
            'partner:id,name',
        ])->orderBy('id', 'desc');

        $result = Pagination::paginate(
            $query,
            $request,
            ['login'],
            ['name', 'id'],
            ['partner_id', 'disabled', 'role'],
        );

        $result['list'] = UserListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }
}
