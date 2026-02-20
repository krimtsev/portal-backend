<?php

namespace App\Http\Controllers\Users;


use App\Http\Controllers\Controller;
use App\Http\Resources\Partner\PartnerResource;
use App\Http\Resources\User\UserProfileResource;
use App\Http\Responses\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $partner = $user->partner;

        $userArray = (new UserProfileResource($user))->toArray($request);
        $partnerArray = $partner ? (new PartnerResource($partner))->toArray($request) : null;

        return JsonResponse::Send([
            'user'    => $userArray,
            'partner' => $partnerArray,
        ]);
    }

    public function update(Request $request): \Illuminate\Http\JsonResponse
    {
        return JsonResponse::Send([
            'user'    => [],
            'partner' => []
        ]);
    }
}
