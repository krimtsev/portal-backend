<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\PartnerNotifications\SendWahelpReportRemindersJob;
use App\Models\Partner\Traits\HasBroadcastDispatch;
use Illuminate\Console\Command;

final class SendWahelpReportRemindersCommand extends Command
{
    use HasBroadcastDispatch;

    protected $signature = 'reports:send-wahelp-reminders
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Рассылка напоминаний о whatsapp филиалам';

    private const IGNORE_CHATS = [
        '-1002027438836', // britva
        '-1002037086197', // soda
    ];

    public function handle(): int
    {
        if (!config('jobs.partner_reminders')) {
            $this->warn('Отчеты отключены в конфигурации.');

            return self::SUCCESS;
        }

        $companyId = $this->option('company_id');

        $partner = config('partner.current', 'default');
        $text = __("partner_notifications.{$partner}.wahelp");

        if ($text === "partner_notifications.{$partner}.wahelp") {
            $this->error("Отсутствует локализация для партнера: {$partner}");

            return self::FAILURE;
        }

        $chatIds = $this->getBroadcastChatIds(self::IGNORE_CHATS, $companyId);

        if ($chatIds->isEmpty()) {
            $this->info('Нет подходящих чатов для рассылки.');

            return self::SUCCESS;
        }

        $this->withProgressBar($chatIds->all(), function (string $chatId) use ($text) {
            SendWahelpReportRemindersJob::dispatch($chatId, $text);
        });

        $this->newLine();
        $this->info(sprintf('Задачи на отправку напоминания успешно добавлены в очередь. Количество: %d', $chatIds->count()));

        return self::SUCCESS;
    }
}
