<?php

namespace App\Console\Commands;

use App\Jobs\Reports\ProcessReturnedClientsReportJob;
use App\Models\Partner\Traits\HasPartnerReportDispatch;
use Illuminate\Console\Command;

final class SendReturnedClientsReportCommand extends Command
{
    use HasPartnerReportDispatch;

    protected $signature = 'report:returned-clients
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Запуск рассылки отчета по повторным клиентам';

    public function handle(): int
    {
        if (!config('jobs.partner_reports')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $partners = $this->getTargetPartners($this->option('company_id'));

        if ($partners->isEmpty()) {
            $this->warn('Нет активных партнеров для рассылки.');

            return self::SUCCESS;
        }

        $totalJobs = $partners->count();
        $this->info("Стартует отправка {$totalJobs} задач в очередь...");

        $bar = $this->output->createProgressBar($totalJobs);
        $bar->start();

        foreach ($partners as $partner) {
            ProcessReturnedClientsReportJob::dispatch($partner);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Все задачи успешно распределены.');

        return self::SUCCESS;
    }
}
