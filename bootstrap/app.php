<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Verificar notificações de revisão diariamente às 8h
        $schedule->command('reviews:check')->dailyAt('08:00');
        // Verificar obrigações legais diariamente às 8h
        $schedule->command('mandatory-events:check')->dailyAt('08:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirecionar erros 403 (não autorizado) para o dashboard com mensagem
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não tem permissão para realizar esta ação.'
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Você não tem permissão para acessar esta página.');
        });
    })->create();
