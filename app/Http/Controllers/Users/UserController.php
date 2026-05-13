<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Pagination\Pagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserCreateRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\User\UserExportResource;
use App\Http\Resources\User\UserListResource;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = User::select(
            'id',
            'login',
            'name',
            'role',
            'partner_id',
            'disabled',
            'last_activity'
        )->with([
            'partner:id,name',
            'access',
        ])->orderBy('id', 'desc');

        $result = Pagination::paginate(
            $query,
            $request,
            ['login', 'name'],
            ['name', 'id'],
            [
                'columns'   => ['partner_id', 'disabled', 'role'],
                'relations' => ['access'],
            ],
        );

        $result['list'] = UserListResource::collection($result['list']);

        return JsonResponse::Send($result);
    }

    public function get(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $user->load([
            'partner:id,name',
            'access',
            'departments'
        ]);

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

        DB::transaction(function () use ($data, $user) {
            $user->update($data);

            if (isset($data['departments'])) {
                $user->departments()->sync($data['departments']);
            }

            if (isset($data['access'])) {
                $user->access()->update($data['access']);
            }
        });

        return JsonResponse::Updated();
    }

    public function create(UserCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        DB::transaction(function () use ($data) {
            $user = User::create($data);

            if (isset($data['departments'])) {
                $user->departments()->sync($data['departments']);
            }
        });

        return JsonResponse::Created();
    }

    public function export(): array
    {
        $users = User::query()
            ->select(
                'id',
                'name',
                'login',
                'role',
                'disabled',
                'last_activity',
                'partner_id'
            )
            ->with('partner:id,name')
            ->orderBy('login')
            ->get();

        return UserExportResource::collection($users)->resolve();
    }
}
