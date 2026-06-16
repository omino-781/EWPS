<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function __construct(protected ActivityLogService $activityLog) {}

    public function handle(Request $request, Closure $next, string $action): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $this->activityLog->log($action, null, [
                'method' => $request->method(),
                'path' => $request->path(),
            ]);
        }

        return $response;
    }
}
