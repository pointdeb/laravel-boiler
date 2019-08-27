<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;

class IsAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->is_admin == false) {
            abort(403);
        }
        return $next($request);
    }
}
