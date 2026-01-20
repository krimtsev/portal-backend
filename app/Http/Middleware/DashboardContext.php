<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardContext
{
    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set('is_dashboard', true);

        return $next($request);
    }
}
