<?php

namespace App\Console\Commands;

use App\Integrations\Yclients\Services\PeriodResolutionService;
use Illuminate\Console\Command;
use Throwable;

final class SyncYcAllCommand extends Command
{

    protected $signature = 'yclients:sync-all
                            {--date= : Конкретный день в формате YYYY-MM-DD}
                            {--company_id= : Конкретный ID компании из YClients (yclients_id)}';

    protected $description = 'Глобальный запуск всех задач синхронизации YClients за день';

    /**
     * Список команд для последовательного выполнения.
     *
     * @var array<string>
     */
    private array $syncCommands = [
        'yclients:sync-staff-work-days',
        'yclients:sync-comments',
        'yclients:sync-records',
        'yclients:sync-transactions',
        'yclients:sync-storage-transactions',
        'yclients:sync-company-daily-stats',
        'yclients:sync-staff-daily-stats',
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

        try {
            $dates = $periodService->resolveFromParams(
                date: $this->option('date')
            );
        } catch (Throwable $e) {
            $this->error('Ошибка параметров: ' . $e->getMessage());

            return self::FAILURE;
        }

        $companyId = $this->option('company_id');

        $this->info(sprintf(
            'Начинаем глобальную синхронизацию. %s',
            $companyId ? "Фильтр по компании: {$companyId}" : 'Для всех активных компаний'
        ));

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

        $this->newLine();
        $this->info('Глобальная синхронизация завершена!');

        return self::SUCCESS;
    }
}
