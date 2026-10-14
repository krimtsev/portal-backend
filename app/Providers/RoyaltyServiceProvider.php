<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Partner\PartnerCode;
use App\Services\Royalty\Contracts\RoyaltyPercentCalculator;
use App\Services\Royalty\RoyaltyStrategyResolver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

final class RoyaltyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            PartnerCode::class,
            static fn (): PartnerCode => PartnerCode::current(),
        );

        $this->app->bind(
            RoyaltyPercentCalculator::class,
            static fn (Application $app): RoyaltyPercentCalculator => $app
                ->make(RoyaltyStrategyResolver::class)
                ->resolve($app->make(PartnerCode::class)),
        );
    }
}
