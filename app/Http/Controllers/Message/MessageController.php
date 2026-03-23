<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message\Message;
use App\Responses\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function list(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        $messages = Message::visibleFor($user)
            ->select('id', 'title', 'description')
            ->get();


        return JsonResponse::Send([
            'list' => $messages
        ]);
    }
}
