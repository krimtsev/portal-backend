<?php

namespace App\Console\Commands;

use App\Jobs\Reports\ProcessNewClientsReportJob;
use App\Models\Partner\Traits\HasPartnerReportDispatch;
use Illuminate\Console\Command;

final class SendNewClientsReportCommand extends Command
{
    use HasPartnerReportDispatch;

    protected $signature = 'report:new-clients
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Запуск рассылки отчета по новым клиентам';

    public function handle(): int
    {
        if (!config('jobs.partner_reports')) {
            $this->warn('Отчеты отключены в конфигурации.');

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
            ProcessNewClientsReportJob::dispatch($partner);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Все задачи успешно распределены.');

        return self::SUCCESS;
    }
}
