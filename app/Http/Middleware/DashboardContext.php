<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class DashboardContext
{
    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('is_dashboard', true);

        return $next($request);
    }
}
