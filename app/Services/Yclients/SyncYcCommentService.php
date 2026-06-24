<?php

declare(strict_types=1);

namespace App\Services\Yclients;

use App\Integrations\Yclients\Resources\Comments\DTO\CommentsFilters;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsResponse;
use App\Integrations\Yclients\YclientsApi;
use App\Models\Yclient\YcComment;

final readonly class SyncYcCommentService
{
    public function __construct(
        private YclientsApi $yclients
    ) {}

    public function sync(int $companyId, string $date): void
    {
        $rawResponse = $this->yclients->comments()->getComments(
            $companyId,
            new CommentsFilters(
                start_date: $date,
                end_date: $date
            )
        );

        $commentsData = $rawResponse['data'] ?? [];

        if (empty($commentsData)) {
            return;
        }

        $upsertData = [];

        foreach ($commentsData as $item) {
            $dto = CommentsResponse::from($item);

            $upsertData[] = [
                'company_id' => $companyId,
                'comment_id' => $dto->id,
                'salon_id'   => $dto->salon_id,
                'staff_id'   => $dto->master_id,
                'type'       => $dto->type,
                'rating'     => $dto->rating,
                'date'       => $dto->date,
            ];
        }

        if (!empty($upsertData)) {
            YcComment::upsert(
                $upsertData,
                [
                    'company_id',
                    'comment_id',
                ],
                [
                    'salon_id',
                    'staff_id',
                    'type',
                    'rating',
                    'date',
                ]
            );
        }
    }
}
