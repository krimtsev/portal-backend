<?php

namespace App\Console\Commands;

use App\Services\Mango\MangoDailyReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class SendDailyMissedCallsReportCommand extends Command
{
    protected $signature = 'report:send-daily-missed-calls
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Рассылка суточных отчетов по пропущенным звонкам Mango';

    public function handle(MangoDailyReportService $reportService): int
    {
        if (!config('jobs.partner_reports')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $targetDateString = $this->option('date') ?? now()->toDateString();

        $date = Carbon::parse($targetDateString);

        $companyId = $this->option('company_id');

        $dispatchedCount = $reportService->dispatchReports($date, $companyId);

        if ($dispatchedCount === 0) {
            $this->info('Нет данных для рассылки или подходящих партнеров.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Задачи на рассылку успешно поставлены (Количество чатов: %d).', $dispatchedCount));

        return self::SUCCESS;
    }
}
