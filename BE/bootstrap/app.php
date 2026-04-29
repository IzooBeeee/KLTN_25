<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('properties:expire')
                 ->dailyAt('00:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {

        // ✅ CORS global — xử lý preflight OPTIONS trước routing
        $middleware->prepend(HandleCors::class);

        // ✅ API group chỉ cần SubstituteBindings
        // KHÔNG dùng EnsureFrontendRequestsAreStateful vì toàn bộ auth dùng Bearer token
        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'AdminMiddleware'    => \App\Http\Middleware\AdminMiddleware::class,
            'KhachHangMiddleware' => \App\Http\Middleware\KhachHangMiddleware::class,
            'MoiGioiMiddleware'  => \App\Http\Middleware\MoiGioiMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();