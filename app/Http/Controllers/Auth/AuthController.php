<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Partner\Partner;
use App\Models\User\User;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AuthController extends Controller
{
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->authenticate();

        return self::userData();
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return JsonResponse::Send(null, trans('auth.logout'));
    }

    public function userData(): \Illuminate\Http\JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return JsonResponse::UserNotFound();
        }

        if ($user->disabled) {
            return JsonResponse::Forbidden();
        }

        $partner = Partner::select('id', 'name', 'disabled')
            ->where('id', $user->partner_id)
            ->first();

        $user->load('access');

        return JsonResponse::Send(
            [
                'user' => [
                    'login'   => $user->login,
                    'role'    => $user->role,
                    'name'    => $user->name,
                    'avatar'  => $user->avatar,
                    'email'   => $user->email,
                    'partner' => $partner,
                ],
                'access' => [
                    'location_map' => $user->access->location_map,
                ],
            ]
        );
    }
}
