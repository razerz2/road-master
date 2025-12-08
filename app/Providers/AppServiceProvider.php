<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Request as Req;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists('Maatwebsite\Excel\Facades\Excel')) {
            \Maatwebsite\Excel\Facades\Excel::macro('sheetName', function ($name) {
                Req::merge(['sheetName' => $name]);
            });
        }

        // Compartilhar nome do módulo atual com todas as views
        view()->composer('*', function ($view) {
            $routeName = \Illuminate\Support\Facades\Route::currentRouteName();
            $moduleName = $this->getModuleName($routeName);
            $view->with('currentModule', $moduleName);
        });
    }

    /**
     * Obtém o nome do módulo baseado na rota atual
     */
    private function getModuleName($routeName)
    {
        if (!$routeName) {
            return null;
        }

        // Mapeamento de rotas para nomes de módulos
        $moduleMap = [
            // Dashboard
            'dashboard' => 'Dashboard',
            
            // Veículos
            'vehicles.index' => 'Veículos',
            'vehicles.create' => 'Veículos - Novo',
            'vehicles.show' => 'Veículos - Detalhes',
            'vehicles.edit' => 'Veículos - Editar',
            
            // Locais
            'locations.index' => 'Locais',
            'locations.create' => 'Locais - Novo',
            'locations.show' => 'Locais - Detalhes',
            'locations.edit' => 'Locais - Editar',
            
            // Percursos
            'trips.index' => 'Percursos',
            'trips.create' => 'Percursos - Novo',
            'trips.show' => 'Percursos - Detalhes',
            'trips.edit' => 'Percursos - Editar',
            
            // Abastecimentos
            'fuelings.index' => 'Abastecimentos',
            'fuelings.create' => 'Abastecimentos - Novo',
            'fuelings.edit' => 'Abastecimentos - Editar',
            
            // Manutenções
            'maintenances.index' => 'Manutenções',
            'maintenances.create' => 'Manutenções - Nova',
            'maintenances.show' => 'Manutenções - Detalhes',
            'maintenances.edit' => 'Manutenções - Editar',
            
            // Notificações de Revisão
            'review-notifications.index' => 'Revisões',
            'review-notifications.create' => 'Revisões - Nova',
            'review-notifications.show' => 'Revisões - Detalhes',
            'review-notifications.edit' => 'Revisões - Editar',
            
            // Usuários
            'users.index' => 'Usuários',
            'users.create' => 'Usuários - Novo',
            'users.show' => 'Usuários - Detalhes',
            'users.edit' => 'Usuários - Editar',
            
            // Relatórios
            'reports.index' => 'Relatórios',
            'reports.km-by-vehicle' => 'Relatórios - KM por Veículo',
            'reports.consumo' => 'Relatórios - Consumo Médio',
            'reports.fuel-cost' => 'Relatórios - Custo de Combustível',
            'reports.fuel-cost-by-vehicle' => 'Relatórios - Custo por Veículo',
            'reports.fuelings' => 'Relatórios - Abastecimentos',
            'reports.maintenances' => 'Relatórios - Manutenções',
            'reports.maintenances-detailed' => 'Relatórios - Manutenções Detalhado',
            'reports.upcoming-maintenance' => 'Relatórios - Manutenções Futuras',
            'reports.driver-usage' => 'Relatórios - Uso por Condutor',
            'reports.odometer-audit' => 'Relatórios - Auditoria de Odômetro',
            'reports.routes-stops' => 'Relatórios - Roteiros e Paradas',
            'reports.ranking' => 'Relatórios - Ranking',
            'reports.consolidated' => 'Relatórios - Consolidado',
            'reports.reviews' => 'Relatórios - Controle de Revisões',
            
            // Importação
            'import.index' => 'Importação',
            'import.progress' => 'Importação - Progresso',
            
            // Notificações
            'notifications.index' => 'Notificações',
            'notifications.show' => 'Notificações - Detalhes',
            
            // Perfil
            'profile.edit' => 'Perfil',
            
            // Configurações
            'settings.index' => 'Configurações',
            'settings.modules.create' => 'Configurações - Novo Módulo',
            'settings.modules.edit' => 'Configurações - Editar Módulo',
            
            // Tipos de Combustível
            'fuel-types.index' => 'Tipos de Combustível',
            
            // Métodos de Pagamento
            'payment-methods.index' => 'Métodos de Pagamento',
            
            // Tipos de Manutenção
            'maintenance-types.index' => 'Tipos de Manutenção',
            
            // Tipos de Local
            'location-types.index' => 'Tipos de Local',
            
            // Postos de Combustível
            'gas-stations.index' => 'Postos de Combustível',
        ];

        // Verificar se a rota exata existe no mapa
        if (isset($moduleMap[$routeName])) {
            return $moduleMap[$routeName];
        }

        // Tentar encontrar por prefixo
        foreach ($moduleMap as $route => $name) {
            if (strpos($routeName, $route . '.') === 0) {
                return $name;
            }
        }

        // Tentar encontrar por prefixo sem o ponto final
        $routeParts = explode('.', $routeName);
        if (count($routeParts) > 0) {
            $baseRoute = $routeParts[0];
            if (isset($moduleMap[$baseRoute])) {
                return $moduleMap[$baseRoute];
            }
        }

        return null;
    }
}
