<?php

namespace App\Http\Middleware;


use Illuminate\Support\Facades\Log;

class LogAfterRequest
{

    protected $log;

    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {

        if ($response->status() !== 500 && !$request->isMethod('get')) {

            if (strpos($request->url(), '/api/v3/admin/menu/food/edit')){
                $requestData = '';
            }
            else{
                $requestData = $request->except('pass', 'password', '__authenticatedUser');
            }

            $info = [
                'headers'      => $request->header('Authorization'),
                'STATUS'       => $response->status(),
                'URL'          => $request->url(),
                'IP'           => $request->ip(),
                'REQUEST'      => $requestData,
                'USER'         => $request->input('__authenticatedUser'),
                'RESPONSE'     => $response->content()
            ];

            if (strpos($request->url(), '/api/resources/')) {
                $info['RESPONSE'] = '';
            }

            if ($response->status() == 400
                or $response->status() == 404
                or $response->status() == 401
                or $response->status() == 500
            ){
                Log::channel('error')->info($info);
            }

            Log::channel('request_response')->info($info);
        }

    }
}
