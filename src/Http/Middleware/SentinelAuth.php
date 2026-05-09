<?php

namespace Chrisquices\Sentinel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SentinelAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
