<?php

namespace Chrisquices\Sentinel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class SentinelAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Gate::denies('viewSentinel')) {
            abort($request->user() ? 403 : 401);
        }

        return $next($request);
    }
}
