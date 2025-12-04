<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\FuelingController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewNotificationController;
use App\Http\Controllers\StorageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rota para servir arquivos do storage (fallback se o link simbólico não existir)
// Esta rota deve estar ANTES do middleware de autenticação para ser pública
// Usando uma rota alternativa para evitar conflitos com o servidor web
Route::get('/files/{path}', [StorageController::class, 'serve'])
    ->where('path', '.*')
    ->name('storage.serve');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Veículos
    Route::resource('vehicles', VehicleController::class);

    // Locais
    Route::resource('locations', LocationController::class);
    Route::post('/locations/store-ajax', [LocationController::class, 'storeAjax'])->name('locations.store-ajax');

    // Percursos
    Route::resource('trips', TripController::class);
    Route::get('/trips/vehicle/{vehicleId}/odometer', [TripController::class, 'getVehicleOdometer'])->name('trips.vehicle.odometer');

    // Abastecimentos
    Route::resource('fuelings', FuelingController::class);

    // Manutenções
    Route::resource('maintenances', MaintenanceController::class);

    // Notificações de Revisão
    Route::resource('review-notifications', ReviewNotificationController::class);
    Route::post('/review-notifications/{reviewNotification}/toggle-active', [ReviewNotificationController::class, 'toggleActive'])->name('review-notifications.toggle-active');

    // Usuários (apenas admin)
    Route::resource('users', UserController::class);

    // Relatórios
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/km-by-vehicle', [ReportController::class, 'kmByVehicle'])->name('km-by-vehicle');
        Route::get('/km-by-vehicle/export/excel', [ReportController::class, 'kmByVehicleExportExcel'])->name('km-by-vehicle.export.excel');
        Route::get('/km-by-vehicle/export/pdf', [ReportController::class, 'kmByVehicleExportPdf'])->name('km-by-vehicle.export.pdf');
        Route::get('/consumo', [ReportController::class, 'consumoMedio'])->name('consumo');
        Route::get('/consumo/export/excel', [ReportController::class, 'consumoMedioExportExcel'])->name('consumo.export.excel');
        Route::get('/consumo/export/pdf', [ReportController::class, 'consumoMedioExportPdf'])->name('consumo.export.pdf');
        Route::get('/fuel-cost', [ReportController::class, 'fuelCost'])->name('fuel-cost');
        Route::get('/fuel-cost-by-vehicle', [ReportController::class, 'fuelCostByVehicle'])->name('fuel-cost-by-vehicle');
        Route::get('/fuelings', [ReportController::class, 'fuelings'])->name('fuelings');
        Route::get('/maintenances', [ReportController::class, 'maintenances'])->name('maintenances');
        Route::get('/maintenances-detailed', [ReportController::class, 'maintenancesDetailed'])->name('maintenances-detailed');
        Route::get('/upcoming-maintenance', [ReportController::class, 'upcomingMaintenance'])->name('upcoming-maintenance');
        Route::get('/driver-usage', [ReportController::class, 'driverUsage'])->name('driver-usage');
        Route::get('/odometer-audit', [ReportController::class, 'odometerAudit'])->name('odometer-audit');
        Route::get('/routes-stops', [ReportController::class, 'routesAndStops'])->name('routes-stops');
        Route::get('/ranking', [ReportController::class, 'ranking'])->name('ranking');
        Route::get('/consolidated', [ReportController::class, 'consolidated'])->name('consolidated');
        Route::get('/reviews', [ReportController::class, 'reviews'])->name('reviews');
    });

    // Importação
    Route::get('/importacao', [ImportController::class, 'index'])->name('import.index');
    Route::post('/importacao', [ImportController::class, 'import'])->name('import.process');
    Route::get('/importacao/exportar', [ImportController::class, 'export'])->name('import.export');
    Route::get('/importacao/progresso/{id}', [ImportController::class, 'progress'])->name('import.progress');
    Route::get('/importacao/status/{id}', [ImportController::class, 'status'])->name('import.status');

    // Notificações
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/api/unread-count', [NotificationController::class, 'unreadCount'])->name('api.unread-count');
        Route::get('/api/latest', [NotificationController::class, 'latest'])->name('api.latest');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Configurações (apenas admin)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'updateSettings'])->name('update');
        Route::match(['put', 'post'], '/appearance', [SettingsController::class, 'updateAppearance'])->name('updateAppearance');
        Route::post('/appearance/reset', [SettingsController::class, 'resetAppearance'])->name('resetAppearance');
        Route::put('/dashboard-preferences', [SettingsController::class, 'updateDashboardPreferences'])->name('updateDashboardPreferences');
        Route::put('/driver-default-modules', [SettingsController::class, 'updateDriverDefaultModules'])->name('updateDriverDefaultModules');
    });

    // Tipos de Combustível (apenas admin)
    Route::prefix('fuel-types')->name('fuel-types.')->group(function () {
        Route::get('/', [\App\Http\Controllers\FuelTypeController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\FuelTypeController::class, 'store'])->name('store');
        Route::put('/{fuelType}', [\App\Http\Controllers\FuelTypeController::class, 'update'])->name('update');
        Route::delete('/{fuelType}', [\App\Http\Controllers\FuelTypeController::class, 'destroy'])->name('destroy');
    });

    // Métodos de Pagamento (apenas admin)
    Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PaymentMethodController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\PaymentMethodController::class, 'store'])->name('store');
        Route::put('/{paymentMethod}', [\App\Http\Controllers\PaymentMethodController::class, 'update'])->name('update');
        Route::delete('/{paymentMethod}', [\App\Http\Controllers\PaymentMethodController::class, 'destroy'])->name('destroy');
    });

    // Tipos de Manutenção (apenas admin)
    Route::prefix('maintenance-types')->name('maintenance-types.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MaintenanceTypeController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\MaintenanceTypeController::class, 'store'])->name('store');
        Route::put('/{maintenanceType}', [\App\Http\Controllers\MaintenanceTypeController::class, 'update'])->name('update');
        Route::delete('/{maintenanceType}', [\App\Http\Controllers\MaintenanceTypeController::class, 'destroy'])->name('destroy');
    });

    // Tipos de Local (apenas admin)
    Route::prefix('location-types')->name('location-types.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LocationTypeController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\LocationTypeController::class, 'store'])->name('store');
        Route::put('/{locationType}', [\App\Http\Controllers\LocationTypeController::class, 'update'])->name('update');
        Route::delete('/{locationType}', [\App\Http\Controllers\LocationTypeController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/auth.php';
