<?php

namespace App\Integrations\Yclients\Resources\Records\DTO;

use App\Integrations\Yclients\Core\ValidateResponse;

/**
 * Получить список записей
 * https://developer.yclients.com/ru/#tag/Zapisi/operation/Получить%20список%20записей
 * $id - ID записи
 * $company_id - Идентификатор компании
 * $staff_id - Идентификатор сотрудника
 * $services - Массив объектов с услугами в записи
 * $goods_transactions - Массив товарных транзакций
 * $staff - Объект данных о сотруднике
 * $client - Данные клиента (может быть пустым)
 * $comer - Данные о посетителе (может быть null)
 * $clients_count - Количество клиентов (В индивидуальной записи всегда = 1)
 * $date - Дата сеанса
 * $datetime - Дата сеанса в ISO
 * $create_date - Дата создания сеанса
 * $technical_break_duration - Длительность технического перерыва
 * $comment - Комментарий к записи
 * $online - (Только при чтении) Запись онлайновая или нет (false если запись внес администратор)
 * $visit_attendance - Статус визита, 2 - Пользователь подтвердил запись, 1 - Пользователь пришел, услуги оказаны, 0 - ожидание пользователя, -1 - пользователь не пришел на визит
 * $attendance - Статус записи, 2 - Пользователь подтвердил запись, 1 - Пользователь пришел, услуги оказаны, 0 - ожидание пользователя, -1 - пользователь не пришел на визит
 * $confirmed - Статус подтверждения записи, 0 - не подтверждена, 1 - подтверждена
 * $seance_length - Длительность сеанса
 * $length - Длительность сеанса
 * $notified - Флаг подтверждения записи администратором филиала, если клиент попросил подтвердить запись
 * $master_request - Был ли указан определенный специалист при записи (false если был указан "не имеет значения")
 * $api_id - Внешний идентификатор записи
 * $from_url - С какой страницы был совершён переход для оформления записи (сайт, приложение ВК и прочее)
 * $review_requested - Флаг запроса у клиента отзыва о посещении
 * $visit_id - Идентификатор визита
 * $created_user_id - Идентификатор пользователя, создавшего запись
 * $deleted - (Только при чтении) Удалена ли запись (true если удалена)
 * $paid_full - Флаг, оплачена ли запись полностью (1 - если оплачена полностью)
 * $prepaid - Доступна ли онлайн-оплата
 * $prepaid_confirmed - Статус онлайн-оплаты
 * $payment_status - Статус автооплаты абонементом (0 - Визит не оплачен, 1 - Визит оплачен, 2 - визит оплачен абонементом автоматически, 3 - Визит не удалось оплатить автоматически, 4 - будет оплачен абонементом автоматически)
 * $last_change_date - Дата последнего редактирования записи
 * $record_labels - Категории записи
 * $activity_id - Идентификатор группового события
 * $custom_fields - Дополнительные поля
 * $documents - Массив документов
 */
final class RecordsResponse extends ValidateResponse
{
    /**
     * @param  ServiceDTO[]  $services
     */
    public function __construct(
        public readonly int $id,
        public readonly int $company_id,
        public readonly int $staff_id,
        public readonly int $visit_id,
        public readonly string $datetime,
        public readonly int $visit_attendance,
        public readonly int $attendance,
        public readonly int $confirmed,
        public readonly int $length,
        public readonly bool $deleted,
        public readonly array $services,
        public readonly ?ClientDTO $client,
        public readonly array $documents,
        public readonly array $goods_transactions
    ) {}

    protected static function rules(): array
    {
        return [
            'id'                 => ['required', 'integer'],
            'company_id'         => ['required', 'integer'],
            'staff_id'           => ['required', 'integer'],
            'visit_id'           => ['required', 'integer'],
            'datetime'           => ['required', 'string'],
            'visit_attendance'   => ['required', 'integer'],
            'attendance'         => ['required', 'integer'],
            'confirmed'          => ['required', 'integer'],
            'length'             => ['required', 'integer'],
            'deleted'            => ['required', 'boolean'],
            'services'           => ['nullable', 'array'],
            'client'             => ['nullable', 'array'],
            'documents'          => ['nullable', 'array'],
            'goods_transactions' => ['nullable', 'array'],
        ];
    }

    protected static function casts(): array
    {
        return [
            'services'           => [ServiceDTO::class],
            'client'             => ClientDTO::class,
            'documents'          => [DocumentDTO::class],
            'goods_transactions' => [GoodsTransactionDTO::class],
        ];
    }

    protected static function build(array $validated): static
    {
        return new self(...$validated);
    }
}
