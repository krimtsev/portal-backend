<?php

namespace App\Integrations\Yclients\Resources\Comments;

use App\Integrations\Yclients\Core\ApiResource;
use App\Integrations\Yclients\Resources\Comments\DTO\CommentsFilters;
use App\Integrations\Yclients\YclientsException;

class CommentsResource extends ApiResource
{
    /**
     * Получить комментарии
     *
     * $filters:
     * $start_date - дата в формате iso8601. Фильтр по дате с (например '2015-09-30')
     * $end_date* - дата в формате iso8601. Фильтр по дате по (например '2015-09-30')
     * $staff_id - ID сотрудника
     * $rating - Оценка в рейтинге
     * $page - Номер страницы
     * $count - Количество отзывов на странице
     *
     * @throws YclientsException
     */
    public function getComments(int $companyId, ?CommentsFilters $filters = null): array
    {
        $query = $filters ? $filters->jsonSerialize() : [];

        return $this->client->get("comments/{$companyId}", $query);
    }
}
