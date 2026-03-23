<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserListResource;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function get(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $user->load(['partner:id,name']);

        return JsonResponse::Send([
            'data' => new UserResource($user),
        ]);
    }

    public function update(UserUpdateRequest $request, User $user): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return JsonResponse::Updated();
    }

    public function create(UserCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return JsonResponse::Created();
    }
}
