<?php

declare(strict_types=1);

namespace App\Jobs\Middleware;

use Closure;

final readonly class ThrottleJobSleep
{
    public function handle(object $job, Closure $next): mixed
    {
        try {
            return $next($job);
        } finally {
            if (config('yclients.job.throttle')) {
                $seconds = (float) ($job->throttleSleep ?? config('yclients.job.throttle_sleep', 1.0));

                if ($seconds > 0) {
                    usleep((int) ($seconds * 1_000_000));
                }
            }
        }
    }
}
