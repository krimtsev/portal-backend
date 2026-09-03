<?php

namespace App\Console\Commands;

use App\Integrations\Yclients\Services\PeriodResolutionService;
use Illuminate\Console\Command;
use Throwable;

final class SyncYcAllCommand extends Command
{

    protected $signature = 'yclients:sync-all
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--month= : Полный месяц в формате YYYY-MM}
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Глобальный запуск всех задач синхронизации YClients за день и месяц';

    /**
     * Список команд для ежедневного выполнения.
     *
     * @var array<string>
     */
    private array $syncCommands = [
        'yclients:sync-staff-work-days',
        'yclients:sync-comments',
        'yclients:sync-records',
        'yclients:sync-transactions',
        'yclients:sync-storage-transactions',
    ];

    /**
     * Список команд для ежемесячного выполнения.
     *
     * @var array<string>
     */
    private array $monthlySyncCommands = [
        'yclients:sync-company-month-stats',
        'yclients:sync-staff-month-stats',
    ];

    /**
     * Исполнение команды
     */
    public function handle(PeriodResolutionService $periodService): int
    {
        if (!config('jobs.yclients')) {
            $this->warn('Синхронизация отключена в конфигурации.');

            return self::SUCCESS;
        }

        $month = $this->option('month');
        $companyId = $this->option('company_id');

        try {
            $dates = $periodService->resolveFromParams(
                date: $this->option('date'),
                month: $month
            );
        } catch (Throwable $e) {
            $this->error('Ошибка параметров: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Начинаем глобальную синхронизацию. Дней для обработки: %d. %s',
            count($dates),
            $companyId ? "Фильтр по компании: {$companyId}" : 'Для всех активных компаний'
        ));

        // Ежедневные синхронизации
        foreach ($dates as $date) {
            $dateString = $date->toDateString();
            $this->warn("\n>>> Запуск за дату: {$dateString}");

            foreach ($this->syncCommands as $command) {
                $this->line("Выполнение: {$command}...");

                $params = ['--date' => $dateString];

                if ($companyId) {
                    $params['--company_id'] = $companyId;
                }

                $resultCode = $this->call($command, $params);

                if ($resultCode !== self::SUCCESS) {
                    $this->error("Ошибка при выполнении {$command} (код {$resultCode})");
                }
            }
        }

        // Ежемесячные синхронизации (запускаются только если передан --month)
        if ($month) {
            $this->warn("\n>>> Запуск ежемесячных задач за месяц: {$month}");

            foreach ($this->monthlySyncCommands as $command) {
                $this->line("Выполнение: {$command}...");

                $params = ['--month' => $month];

                if ($companyId) {
                    $params['--company_id'] = $companyId;
                }

                $resultCode = $this->call($command, $params);

                if ($resultCode !== self::SUCCESS) {
                    $this->error("Ошибка при выполнении {$command} (код {$resultCode})");
                }
            }
        }

        $this->newLine();
        $this->info('Глобальная синхронизация завершена!');

        return self::SUCCESS;
    }
}
