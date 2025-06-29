<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\JsonResponse;
use App\Models\Partner;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->authenticate();

        return self::userData();
    }

    public function logout(): \Illuminate\Http\JsonResponse
    {
        if (Auth::check()) {
            Auth::logout();
        }

        return JsonResponse::Send(null, 'Logged out');
    }

    public function userData(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return JsonResponse::UserNotFound();
        }

        $partner = Partner::select("id", "name", "disabled")
            ->where('id', $user->partner_id)
            ->first();

        if ($user->disabled) {
            return JsonResponse::Forbidden();
        }

        return JsonResponse::Send(
            [
                'user' => [
                    'login'   => $user->login,
                    'role'    => $user->role,
                    'name'    => $user->name,
                    'avatar'  => $user->avatar,
                    'email'   => $user->email,
                    'partner' => $partner
                ],
            ]
        );
    }
}
