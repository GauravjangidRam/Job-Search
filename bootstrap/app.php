<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->renderable(function (QueryException $e, Request $request) {
            $connectionErrorCodes = [2002, 1045, 1044, 1049, 2003, 2005, 2006, 2013];

            if (in_array($e->errorInfo[1] ?? null, $connectionErrorCodes)) {
                return response()->view('errors.503', [], 503);
            }
        });
        $exceptions->renderable(function (\PDOException $e, Request $request) {
            $connectionErrorCodes = [2002, 1045, 1044, 1049, 2003, 2005, 2006, 2013];

            if (in_array((int) $e->getCode(), $connectionErrorCodes)) {
                return response()->view('errors.503', [], 503);
            }
        });
    })->create();