<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
        ]);

        // All submitted numbers are stored with English digits (०-९ → 0-9).
        $middleware->append(\App\Http\Middleware\ConvertDigitsToEnglish::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // An upload bigger than the server's PHP limit: friendly message on the same page,
        // not a bare "413" error page.
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            $limit = ini_get('post_max_size');
            $message = "The file is too large to upload. The server accepts up to {$limit} per upload.";

            return $request->expectsJson()
                ? response()->json(['message' => $message], 413)
                : redirect()->back()->with('error', $message)->withErrors(['file' => $message]);
        });
    })->create();
