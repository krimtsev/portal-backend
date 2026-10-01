<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HorizonBasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        // В локальном окружении пропускаем без пароля
        if (app()->environment('local')) {
            return $next($request);
        }

        $username = config('horizon.username', '');
        $password = config('horizon.password', '');

        if ($request->getUser() !== $username || $request->getPassword() !== $password) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Horizon Dashboard"'
            ]);
        }

        return $next($request);
    }
}
