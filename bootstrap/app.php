<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Schema;

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
        // Verificar se a tabela system_settings existe antes de tentar acessá-la
        // Isso evita erros durante a execução de migrações
        if (!Schema::hasTable('system_settings')) {
            return;
        }
        
        // Obter configurações de horário (com fallback para 08:00)
        $reviewCheckTime = \App\Models\SystemSetting::get('review_check_time', '08:00');
        $mandatoryEventCheckTime = \App\Models\SystemSetting::get('mandatory_event_check_time', '08:00');
        
        // Verificar frequência de verificação
        $checkFrequency = \App\Models\SystemSetting::get('notification_check_frequency', 'daily');
        
        // Agendar verificação de revisões
        if ($checkFrequency === 'daily') {
            $schedule->command('reviews:check')->dailyAt($reviewCheckTime);
        } else {
            $schedule->command('reviews:check')->weeklyOn(1, $reviewCheckTime); // Segunda-feira
        }
        
        // Agendar verificação de obrigações legais
        if ($checkFrequency === 'daily') {
            $schedule->command('mandatory-events:check')->dailyAt($mandatoryEventCheckTime);
        } else {
            $schedule->command('mandatory-events:check')->weeklyOn(1, $mandatoryEventCheckTime); // Segunda-feira
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Redirecionar erros 403 (não autorizado) para o dashboard com mensagem intuitiva
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não tem permissão para realizar esta ação.'
                ], 403);
            }

            // Mapeamento de rotas para nomes de módulos amigáveis
            $routeModuleMap = [
                'vehicles' => 'Veículos',
                'trips' => 'Percursos',
                'fuelings' => 'Abastecimentos',
                'maintenances' => 'Manutenções',
                'locations' => 'Locais',
                'mandatory-events' => 'Obrigações Legais',
                'review-notifications' => 'Notificações de Revisão',
                'users' => 'Usuários',
                'settings' => 'Configurações',
                'notifications' => 'Notificações',
                'reports' => 'Relatórios',
                'import' => 'Importação',
            ];

            // Tentar identificar o módulo pela rota
            $routeName = $request->route()?->getName();
            $moduleName = null;
            
            if ($routeName) {
                // Extrair o nome do módulo da rota (ex: vehicles.index -> vehicles)
                $routeParts = explode('.', $routeName);
                $routePrefix = $routeParts[0] ?? null;
                
                if ($routePrefix && isset($routeModuleMap[$routePrefix])) {
                    $moduleName = $routeModuleMap[$routePrefix];
                }
            }
            
            // Se não encontrou pela rota, tentar pela URL
            if (!$moduleName) {
                $path = trim($request->path(), '/');
                $pathParts = explode('/', $path);
                $firstSegment = $pathParts[0] ?? null;
                
                if ($firstSegment && isset($routeModuleMap[$firstSegment])) {
                    $moduleName = $routeModuleMap[$firstSegment];
                }
            }

            // Criar mensagem personalizada
            if ($moduleName) {
                $message = "Você não tem permissão para acessar o módulo \"{$moduleName}\". Entre em contato com o administrador do sistema para solicitar acesso.";
            } else {
                $message = "Você não tem permissão para acessar esta página. Entre em contato com o administrador do sistema para solicitar acesso.";
            }

            return redirect()->route('dashboard')
                ->with('error', $message);
        });
    })->create();
