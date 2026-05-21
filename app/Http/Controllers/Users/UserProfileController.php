<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Partner\PartnerResource;
use App\Http\Resources\User\UserProfileResource;
use App\Responses\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $user->load(['departments', 'partner']);

        $userArray = new UserProfileResource($user);
        $partnerArray = $user->partner
            ? new PartnerResource($user->partner)
            : null;

        return JsonResponse::Send([
            'user'    => $userArray,
            'partner' => $partnerArray,
        ]);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'user'    => [],
            'partner' => [],
        ]);
    }
}
