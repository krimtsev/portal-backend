<?php

namespace App\Providers;

use App\Integrations\Mango\MangoApi;
use App\Integrations\Telegram\TelegramManager;
use App\Integrations\Telegram\Transport\TelegramTransport;
use App\Integrations\Yclients\YclientsApi;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(YclientsApi::class);

        $this->app->singleton(TelegramManager::class, function () {
            $config = config('telegram');
            $transport = new TelegramTransport($config['proxy'] ?? []);

            return new TelegramManager($config, $transport);
        });

        $this->app->singleton(MangoApi::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** Запросы в базу (для отладки) */
        if (config('app.database_log')) {
            DB::listen(function ($query) {
                Log::build([
                    'driver' => 'single',
                    'path'   => storage_path('logs/database.log'),
                ])->info("Execution Time: {$query->time}ms\nSQL: {$query->sql}", [
                    'bindings' => $query->bindings,
                ]);
            });
        }

        /** Ограничения кол-ва писем за час */
        RateLimiter::for('external_mailer', function (object $job) {
            $limit = config('mail.rate_limit.per_hour', 100);

            return Limit::perHour($limit);
        });

        /** Ошибки запуска команд по расписанию */
        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {
            Log::channel('scheduler')->error('Scheduler task failed', [
                'command'    => $event->task->command ?? $event->task->description,
                'expression' => $event->task->expression,
                'exception'  => $event->exception->getMessage(),
                'file'       => $event->exception->getFile() . ':' . $event->exception->getLine(),
                'trace'      => $event->exception->getTraceAsString(),
            ]);
        });

        /** Для правильной работы email шаблонов */
        Blade::anonymousComponentPath(
            resource_path('views/emails/components'),
            'emails'
        );
    }
}
