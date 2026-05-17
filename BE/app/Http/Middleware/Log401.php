<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Log401
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        if ($response->status() === 401) {
            Log::error('401 Unauthorized on: ' . $request->url(), [
                'headers' => $request->headers->all(),
                'method' => $request->method(),
                'bearer' => $request->bearerToken()
            ]);
        }
        return $response;
    }
}
