<?php

namespace App\Console\Commands;

use App\Jobs\Yclients\SyncCompanyStaffJob;
use App\Models\Partner\Partner;
use Illuminate\Console\Command;

class SyncYcCompanyStaffCommand extends Command
{
    protected $signature = 'yclients:sync-company-staff
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Синхронизация сотрудников компании из YClients';

    public function handle(): int
    {
        if (!config('jobs.yclients')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $query = Partner::query()->withActiveYclients();

        if ($companyId = $this->option('company_id')) {
            $query->where('yclients_id', $companyId);
        }

        $partners = $query->get(['id', 'yclients_id']);

        if ($partners->isEmpty()) {
            $this->warn('Нет активных партнеров с привязанным yclients_id для обработки.');

            return self::SUCCESS;
        }

        $totalJobs = $partners->count();
        $this->info('Активных партнеров: ' . $partners->count());
        $this->info("Стартует отправка {$totalJobs} задач в очередь...");

        $bar = $this->output->createProgressBar($totalJobs);
        $bar->start();

        foreach ($partners as $partner) {
            SyncCompanyStaffJob::dispatch(
                (int) $partner->yclients_id,
            )->delay(3);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Все задачи успешно распределены.');

        return self::SUCCESS;
    }
}
