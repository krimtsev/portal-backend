<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePassword\ChangePasswordRequest;
use App\Notifications\User\PasswordChangedNotification;
use App\Responses\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class ChangePasswordController extends Controller
{
    function update(ChangePasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User\User $user */
        $user = Auth::user();

        $user->password = Hash::make($request->password);
        $user->save();

        if (!empty($user->email)) {
             Notification::send($user, new PasswordChangedNotification($user));
        }

        return JsonResponse::Send([
            'changed' => true,
        ]);
    }
}
