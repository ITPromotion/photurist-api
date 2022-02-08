<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserBlock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        dd(\Auth::user());
        return $next($request);
    }
}
