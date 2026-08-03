<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserDataResource;
use App\Models\User\User;
use App\Responses\JsonResponse;
use App\Services\Maintenance\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AuthController extends Controller
{
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $request->authenticate();

        return $this->userData();
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

        $user->load(['access', 'partner']);

        $responseData = [
            'data' => (new UserDataResource($user))->resolve(),
        ];

        $isMaintenance = (new MaintenanceService())->isEnabled();
        if ($isMaintenance) {
            $responseData['maintenance'] = [
                'enabled' => $isMaintenance,
            ];
        }

        return JsonResponse::Send($responseData);
    }
}
