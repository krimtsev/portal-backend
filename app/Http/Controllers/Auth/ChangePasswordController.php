<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePassword\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Responses\JsonResponse;

class ChangePasswordController extends Controller
{
    function update(ChangePasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User\User $user */
        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->save();

        return JsonResponse::Send([
            'changed' => true,
        ]);
    }
}
