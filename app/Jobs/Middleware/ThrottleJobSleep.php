<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use Closure;

final readonly class ThrottleJobSleep
{
    public function __construct(
        private ?bool $enabled = null,
        private ?float $sleep = null,
    ) {}

    public static function fromConfig(string $enabledKey, string $sleepKey): self
    {
        return new self(
            enabled: config($enabledKey),
            sleep: config($sleepKey),
        );
    }

    public static function forYclients(): self
    {
        return new self(
            enabled: config('yclients.queue.throttle.enabled'),
            sleep: config('yclients.queue.throttle.sleep'),
        );
    }

    public static function forTelegram(): self
    {
        return new self(
            enabled: config('telegram.queue.throttle.enabled'),
            sleep: config('telegram.queue.throttle.sleep'),
        );
    }

    public function handle(object $job, Closure $next): mixed
    {
        try {
            return $next($job);
        } finally {
            $enabled = $this->enabled ?? (bool) config('queue.throttle.enabled', false);

            if ($enabled) {
                $defaultSleep = $this->sleep ?? (float) config('queue.throttle.sleep', 1.0);
                $jobMinSleep = (float) ($job->minThrottleSleep ?? 0.0);
                $seconds = max($defaultSleep, $jobMinSleep);

                if ($seconds > 0) {
                    usleep((int) ($seconds * 1_000_000));
                }
            }
        }
    }
}
