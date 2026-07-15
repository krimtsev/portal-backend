<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Responses\JsonResponse;
use App\Services\Message\MessageService;
use App\Services\Statistics\StatisticsPartnerService;
use Illuminate\Support\Facades\Auth;

final class AppController extends Controller
{
    public function __construct(
        private readonly StatisticsPartnerService $statisticsService,
        private readonly MessageService $messageService
    ) {}

    public function homeData(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        return JsonResponse::Send([
            'finances' => $this->statisticsService->getPartnerFinanceService($user),
            'messages' => $this->messageService->getActiveMessagesForUser($user),
        ]);
    }
}
