<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestTelemetry
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->headers->get('X-Request-Id') ?: (string) Str::uuid();
        $startedAt = hrtime(true);
        $response = $next($request);
        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;

        $response->headers->set('X-Request-Id', $requestId);

        if ($response->getStatusCode() >= 500 || $durationMs >= 1_000) {
            Log::warning('HTTP request completed.', [
                'request_id' => $requestId,
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
                'duration_ms' => round($durationMs, 2),
                'user_id' => $request->user()?->id,
            ]);
        }

        return $response;
    }
}
