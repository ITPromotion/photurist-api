<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserBlock
{
    const Block = 'block';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (\Auth::user()->status == self::Block) {
            return response()->json([ 'errors' => ['user blocked']], 403);
        }
        return $next($request);
    }
}
