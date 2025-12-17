<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripStop;
use App\Models\Fueling;
use App\Models\Vehicle;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\ReviewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportsExport;

class ReportController extends Controller
{
    private function checkReportsPermission()
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && !$user->hasPermission('reports', 'view')) {
            abort(403, 'Você não tem permissão para acessar relatórios.');
        }
    }

    public function index()
    {
        $this->checkReportsPermission();
        
        // Ícone padrão para todos os relatórios (clipboard com checklist)
        $defaultIcon = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4';

        $reports = [
            [
                'title' => 'KM por Veículo',
                'description' => 'Visualize a quilometragem total rodada por cada veículo em um período determinado.',
                'route' => 'reports.km-by-vehicle',
                'icon' => $defaultIcon,
                'color' => 'from-blue-500 to-cyan-500',
            ],
            [
                'title' => 'Consumo Médio (KM/L)',
                'description' => 'Analise a eficiência de combustível de cada veículo calculando o consumo médio em quilômetros por litro.',
                'route' => 'reports.consumo',
                'icon' => $defaultIcon,
                'color' => 'from-green-500 to-emerald-500',
            ],
            [
                'title' => 'Custo de Combustível',
                'description' => 'Monitore os gastos com combustível por veículo e motorista para controle financeiro.',
                'route' => 'reports.fuel-cost',
                'icon' => $defaultIcon,
                'color' => 'from-yellow-500 to-orange-500',
            ],
            [
                'title' => 'Abastecimentos',
                'description' => 'Lista completa de abastecimentos com detalhes de data, posto, litros e valores.',
                'route' => 'reports.fuelings',
                'icon' => $defaultIcon,
                'color' => 'from-purple-500 to-pink-500',
            ],
            [
                'title' => 'Manutenções',
                'description' => 'Relatório gerencial de manutenções com resumo por tipo e lista detalhada.',
                'route' => 'reports.maintenances',
                'icon' => $defaultIcon,
                'color' => 'from-red-500 to-rose-500',
            ],
            [
                'title' => 'Manutenções Detalhado',
                'description' => 'Relatório completo com fornecedor, custo, KM na manutenção e próxima revisão prevista.',
                'route' => 'reports.maintenances-detailed',
                'icon' => $defaultIcon,
                'color' => 'from-indigo-500 to-blue-500',
            ],
            [
                'title' => 'Manutenções Futuras',
                'description' => 'Visualize manutenções previstas por data e KM, com alertas para itens próximos e atrasados.',
                'route' => 'reports.upcoming-maintenance',
                'icon' => $defaultIcon,
                'color' => 'from-amber-500 to-yellow-500',
            ],
            [
                'title' => 'Uso da Frota (Condutor)',
                'description' => 'Analise a utilização da frota por motorista: KM rodado, quantidade de percursos e médias.',
                'route' => 'reports.driver-usage',
                'icon' => $defaultIcon,
                'color' => 'from-teal-500 to-cyan-500',
            ],
            [
                'title' => 'Auditoria de Odômetro',
                'description' => 'Identifique inconsistências no odômetro: KM regressivos, gaps suspeitos e problemas de registro.',
                'route' => 'reports.odometer-audit',
                'icon' => $defaultIcon,
                'color' => 'from-orange-500 to-red-500',
            ],
            [
                'title' => 'Revisões (Notificações)',
                'description' => 'Controle de revisões programadas com status de notificações e KM restantes até a próxima.',
                'route' => 'reports.reviews',
                'icon' => $defaultIcon,
                'color' => 'from-violet-500 to-purple-500',
            ],
            [
                'title' => 'Roteiros e Paradas',
                'description' => 'Analise rotas mais frequentes, paradas intermediárias e tempo médio de viagem.',
                'route' => 'reports.routes-stops',
                'icon' => $defaultIcon,
                'color' => 'from-pink-500 to-rose-500',
            ],
            [
                'title' => 'Ranking Geral',
                'description' => 'Rankings dos motoristas que mais rodaram, veículos mais usados, postos mais utilizados e tipos de manutenção.',
                'route' => 'reports.ranking',
                'icon' => $defaultIcon,
                'color' => 'from-emerald-500 to-teal-500',
            ],
            [
                'title' => 'Relatório Consolidado',
                'description' => 'Visão completa com veículos, KM, manutenções, abastecimentos, consumo médio e TCO (Total Cost of Ownership).',
                'route' => 'reports.consolidated',
                'icon' => $defaultIcon,
                'color' => 'from-slate-500 to-gray-600',
            ],
        ];

        return view('reports.index', compact('reports'));
    }

    public function kmByVehicle(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $vehicles = Vehicle::where('active', true)->get();

        $results = [];
        foreach ($vehicles as $vehicle) {
            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);

            $results[] = [
                'vehicle' => $vehicle,
                'total_km' => $consumptionData['total_km'],
                'trip_count' => $trips->count(),
                'total_liters' => $consumptionData['total_liters'],
                'real_consumption' => $consumptionData['real_consumption'],
                'period_consumption' => $consumptionData['period_consumption'],
                'full_count' => $consumptionData['full_count'],
            ];
        }

        return view('reports.km-by-vehicle', compact('results', 'startDate', 'endDate'));
    }

    public function fuelCostByVehicle(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $results = Fueling::select('vehicle_id', DB::raw('SUM(total_amount) as total_cost'))
            ->whereBetween('date_time', [$startDate, $endDate])
            ->groupBy('vehicle_id')
            ->with('vehicle')
            ->get();

        return view('reports.fuel-cost-by-vehicle', compact('results', 'startDate', 'endDate'));
    }

    public function maintenances(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with(['vehicle', 'user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        $byType = $maintenances->groupBy('type')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total_cost' => $items->sum('cost'),
            ];
        });

        $vehicles = Vehicle::where('active', true)->get();

        return view('reports.maintenances', compact('maintenances', 'byType', 'vehicles', 'startDate', 'endDate', 'vehicleId'));
    }

    // 1. Consumo Médio (KM/L) por Veículo
    public function consumoMedio(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $vehicles = Vehicle::where('active', true)->get();
        $results = [];

        foreach ($vehicles as $vehicle) {
            if ($vehicleId && $vehicle->id != $vehicleId) {
                continue;
            }

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);

            if ($consumptionData['total_km'] > 0 || $consumptionData['total_liters'] > 0) {
                $results[] = [
                    'vehicle' => $vehicle,
                    'total_km' => $consumptionData['total_km'],
                    'total_liters' => $consumptionData['total_liters'],
                    'real_consumption' => $consumptionData['real_consumption'],
                    'period_consumption' => $consumptionData['period_consumption'],
                    'full_count' => $consumptionData['full_count'],
                ];
            }
        }

        return view('reports.consumo', compact('results', 'startDate', 'endDate', 'vehicles', 'vehicleId'));
    }

    // 2. Custo de Combustível (por Veículo/Motorista)
    public function fuelCost(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');
        $driverId = $request->input('driver_id');

        $query = Fueling::select('vehicle_id', 'user_id', DB::raw('SUM(total_amount) as total_cost'), DB::raw('SUM(liters) as total_liters'))
            ->whereBetween('date_time', [$startDate, $endDate])
            ->with(['vehicle', 'user']);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($driverId) {
            $query->where('user_id', $driverId);
        }

        $results = $query->groupBy('vehicle_id', 'user_id')->get();

        $vehicles = Vehicle::where('active', true)->get();
        $drivers = User::where('role', 'condutor')->where('active', true)->get();

        return view('reports.fuel_cost', compact('results', 'startDate', 'endDate', 'vehicles', 'drivers', 'vehicleId', 'driverId'));
    }

    // 3. Resumo de Abastecimentos
    public function fuelings(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');
        $fuelType = $request->input('fuel_type');

        $query = Fueling::with(['vehicle', 'user'])
            ->whereBetween('date_time', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }

        $fuelings = $query->orderBy('date_time', 'desc')->get();

        $totalAmount = $fuelings->sum('total_amount');
        $totalLiters = $fuelings->sum('liters');

        $vehicles = Vehicle::where('active', true)->get();
        $fuelTypes = Fueling::distinct()->pluck('fuel_type')->filter();

        return view('reports.fuelings', compact('fuelings', 'startDate', 'endDate', 'vehicles', 'fuelTypes', 'vehicleId', 'fuelType', 'totalAmount', 'totalLiters'));
    }

    // 4. Manutenções Detalhado (já existe, mas vamos melhorar)
    // O método maintenances já existe, mas vamos criar um método separado para o detalhado
    public function maintenancesDetailed(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with(['vehicle', 'maintenanceType', 'user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->orderBy('date', 'desc')->get();

        $vehicles = Vehicle::where('active', true)->get();

        return view('reports.maintenances_detailed', compact('maintenances', 'vehicles', 'startDate', 'endDate', 'vehicleId'));
    }

    // 5. Manutenções Futuras
    public function upcomingMaintenance(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with('vehicle')
            ->where(function($q) {
                $q->whereNotNull('next_due_date')
                  ->orWhereNotNull('next_due_odometer');
            });

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        $upcomingByDate = [];
        $upcomingByKm = [];
        $overdue = [];

        foreach ($maintenances as $maintenance) {
            $vehicle = $maintenance->vehicle;
            $currentKm = $vehicle->current_odometer ?? 0;
            $today = Carbon::today();

            // Verificar por data
            if ($maintenance->next_due_date) {
                $daysUntil = $today->diffInDays($maintenance->next_due_date, false);
                if ($daysUntil < 0) {
                    $overdue[] = [
                        'maintenance' => $maintenance,
                        'type' => 'date',
                        'days_overdue' => abs($daysUntil),
                    ];
                } elseif ($daysUntil <= 30) {
                    $upcomingByDate[] = [
                        'maintenance' => $maintenance,
                        'days_until' => $daysUntil,
                    ];
                }
            }

            // Verificar por KM
            if ($maintenance->next_due_odometer) {
                $kmUntil = $maintenance->next_due_odometer - $currentKm;
                if ($kmUntil < 0) {
                    $overdue[] = [
                        'maintenance' => $maintenance,
                        'type' => 'km',
                        'km_overdue' => abs($kmUntil),
                    ];
                } elseif ($kmUntil <= 1000) {
                    $upcomingByKm[] = [
                        'maintenance' => $maintenance,
                        'km_until' => $kmUntil,
                        'current_km' => $currentKm,
                    ];
                }
            }
        }

        $vehicles = Vehicle::where('active', true)->get();

        return view('reports.upcoming_maintenance', compact('upcomingByDate', 'upcomingByKm', 'overdue', 'vehicles', 'vehicleId'));
    }

    // 6. Uso da Frota por Condutor
    public function driverUsage(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $driverId = $request->input('driver_id');

        $query = Trip::select('driver_id', 
                DB::raw('SUM(km_total) as total_km'),
                DB::raw('COUNT(*) as trip_count'),
                DB::raw('AVG(km_total) as avg_km_per_trip'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('driver')
            ->groupBy('driver_id');

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        $results = $query->get();

        // Calcular média mensal
        $daysInPeriod = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $monthsInPeriod = $daysInPeriod / 30;

        foreach ($results as $result) {
            $result->avg_km_per_month = $monthsInPeriod > 0 ? round($result->total_km / $monthsInPeriod, 2) : 0;
        }

        $drivers = User::where('role', 'condutor')->where('active', true)->get();

        return view('reports.driver_usage', compact('results', 'startDate', 'endDate', 'drivers', 'driverId'));
    }

    // 7. Auditoria de Odômetro
    public function odometerAudit(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = Trip::with('vehicle');

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $trips = $query->orderBy('vehicle_id')->orderBy('date')->orderBy('odometer_start')->get();

        $auditResults = [];
        $previousTrip = null;

        foreach ($trips as $trip) {
            $issues = [];

            if ($previousTrip && $previousTrip->vehicle_id == $trip->vehicle_id) {
                // Verificar KM regressivo
                if ($trip->odometer_start < $previousTrip->odometer_end) {
                    $issues[] = 'KM regressivo detectado';
                }

                // Verificar gaps suspeitos (diferença muito grande)
                $expectedKm = $trip->odometer_start - $previousTrip->odometer_end;
                if ($expectedKm < 0) {
                    $issues[] = 'KM inicial menor que KM final anterior';
                } elseif ($expectedKm > 10000) {
                    $issues[] = 'Gap suspeito: ' . number_format($expectedKm, 0, ',', '.') . ' km';
                }
            }

            // Verificar se odômetro inicial é maior que final
            if ($trip->odometer_start > $trip->odometer_end) {
                $issues[] = 'Odômetro inicial maior que final';
            }

            if (!empty($issues) || !$vehicleId) {
                $auditResults[] = [
                    'trip' => $trip,
                    'issues' => $issues,
                    'previous_trip' => $previousTrip,
                ];
            }

            $previousTrip = $trip;
        }

        $vehicles = Vehicle::where('active', true)->get();

        return view('reports.odometer_audit', compact('auditResults', 'vehicles', 'vehicleId'));
    }

    // 8. Roteiros e Paradas
    public function routesAndStops(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Trip::with(['vehicle', 'originLocation', 'destinationLocation', 'stops.location'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $trips = $query->orderBy('date', 'desc')->get();

        // Agrupar rotas mais frequentes
        $routes = [];
        foreach ($trips as $trip) {
            $key = $trip->origin_location_id . '-' . $trip->destination_location_id;
            if (!isset($routes[$key])) {
                $routes[$key] = [
                    'origin' => $trip->originLocation,
                    'destination' => $trip->destinationLocation,
                    'count' => 0,
                    'total_km' => 0,
                    'avg_time' => 0,
                ];
            }
            $routes[$key]['count']++;
            $routes[$key]['total_km'] += $trip->km_total;
            
            if ($trip->departure_time && $trip->return_time) {
                try {
                    // Formatar o time corretamente (remover zeros extras e garantir formato H:i:s)
                    $departureTime = $this->formatTime($trip->departure_time);
                    $returnTime = $this->formatTime($trip->return_time);
                    
                    if ($departureTime && $returnTime) {
                        $departure = Carbon::parse($trip->date->format('Y-m-d') . ' ' . $departureTime);
                        $return = Carbon::parse($trip->date->format('Y-m-d') . ' ' . $returnTime);
                $routes[$key]['avg_time'] += $departure->diffInMinutes($return);
                    }
                } catch (\Exception $e) {
                    // Ignorar erros de parse de time e continuar
                    continue;
                }
            }
        }

        // Calcular média de tempo
        foreach ($routes as $key => $route) {
            if ($route['count'] > 0) {
                $routes[$key]['avg_time'] = round($route['avg_time'] / $route['count'], 0);
            }
        }

        // Paradas intermediárias mais comuns
        $stops = [];
        foreach ($trips as $trip) {
            foreach ($trip->stops as $stop) {
                $locationId = $stop->location_id;
                if (!isset($stops[$locationId])) {
                    $stops[$locationId] = [
                        'location' => $stop->location,
                        'count' => 0,
                    ];
                }
                $stops[$locationId]['count']++;
            }
        }

        usort($stops, function($a, $b) {
            return $b['count'] - $a['count'];
        });

        $vehicles = Vehicle::where('active', true)->get();

        return view('reports.routes_stops', compact('trips', 'routes', 'stops', 'startDate', 'endDate', 'vehicles', 'vehicleId'));
    }

    // 9. Ranking Geral
    public function ranking(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Motoristas que mais rodaram
        $topDrivers = Trip::select('driver_id', DB::raw('SUM(km_total) as total_km'), DB::raw('COUNT(*) as trip_count'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('driver')
            ->groupBy('driver_id')
            ->orderBy('total_km', 'desc')
            ->limit(10)
            ->get();

        // Veículos mais usados
        $topVehicles = Trip::select('vehicle_id', DB::raw('SUM(km_total) as total_km'), DB::raw('COUNT(*) as trip_count'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('vehicle')
            ->groupBy('vehicle_id')
            ->orderBy('total_km', 'desc')
            ->limit(10)
            ->get();

        // Postos mais usados
        $topGasStations = Fueling::select('gas_station_name', 
                DB::raw('COUNT(*) as fueling_count'),
                DB::raw('SUM(liters) as total_liters'),
                DB::raw('SUM(total_amount) as total_amount'))
            ->whereBetween('date_time', [$startDate, $endDate])
            ->whereNotNull('gas_station_name')
            ->where('gas_station_name', '!=', '')
            ->groupBy('gas_station_name')
            ->orderBy('fueling_count', 'desc')
            ->limit(10)
            ->get();

        // Tipos de manutenção mais realizados
        $topMaintenanceTypes = Maintenance::select('type',
                DB::raw('COUNT(*) as maintenance_count'),
                DB::raw('SUM(cost) as total_cost'))
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('type')
            ->groupBy('type')
            ->orderBy('maintenance_count', 'desc')
            ->limit(10)
            ->get();

        return view('reports.ranking', compact('topDrivers', 'topVehicles', 'topGasStations', 'topMaintenanceTypes', 'startDate', 'endDate'));
    }

    // 10. Relatório Consolidado
    public function consolidated(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $vehicles = Vehicle::where('active', true)->get();
        $results = [];

        foreach ($vehicles as $vehicle) {
            if ($vehicleId && $vehicle->id != $vehicleId) {
                continue;
            }

            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $maintenances = Maintenance::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);
            $totalFuelCost = $fuelings->sum('total_amount');
            $totalMaintenanceCost = $maintenances->sum('cost');
            $tco = $totalFuelCost + $totalMaintenanceCost;

            $results[] = [
                'vehicle' => $vehicle,
                'total_km' => $consumptionData['total_km'],
                'trip_count' => $trips->count(),
                'total_liters' => $consumptionData['total_liters'],
                'real_consumption' => $consumptionData['real_consumption'],
                'period_consumption' => $consumptionData['period_consumption'],
                'full_count' => $consumptionData['full_count'],
                'total_fuel_cost' => $totalFuelCost,
                'maintenance_count' => $maintenances->count(),
                'total_maintenance_cost' => $totalMaintenanceCost,
                'tco' => $tco,
            ];
        }

        return view('reports.consolidated', compact('results', 'startDate', 'endDate', 'vehicles', 'vehicleId'));
    }

    // 11. Relatório de Revisões / Controle de Revisões
    public function reviews(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = ReviewNotification::with('vehicle')
            ->where('active', true);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $reviews = $query->get();

        $reviewData = [];
        foreach ($reviews as $review) {
            $vehicle = $review->vehicle;
            $currentKm = $vehicle->current_odometer ?? 0;
            $kmUntil = $review->notification_km - $currentKm;
            $status = 'ok';

            if ($kmUntil < 0) {
                $status = 'overdue';
            } elseif ($kmUntil <= 500) {
                $status = 'warning';
            }

            $reviewData[] = [
                'review' => $review,
                'current_km' => $currentKm,
                'km_until' => $kmUntil,
                'status' => $status,
            ];
        }

        $vehicles = Vehicle::where('active', true)->get();

        return view('reports.reviews', compact('reviewData', 'vehicles', 'vehicleId'));
    }

    // Métodos auxiliares para exportação
    private function exportToExcel($data, $headings, $title, $filename)
    {
        $export = new class($data, $title, $headings) extends ReportsExport {
            public function map($row): array
            {
                if (is_array($row)) {
                    // Se for array associativo, retornar valores na ordem dos headings
                    $result = [];
                    foreach ($this->headings as $heading) {
                        $result[] = $row[$heading] ?? '';
                    }
                    return $result;
                }
                return (array) $row;
            }
        };

        return Excel::download($export, $filename . '.xlsx');
    }

    private function exportToPDF($data, $headings, $title, $viewName, $filename)
    {
        // Converter array associativo para array indexado
        $tableData = [];
        foreach ($data as $row) {
            $tableRow = [];
            foreach ($headings as $heading) {
                $tableRow[] = $row[$heading] ?? '-';
            }
            $tableData[] = $tableRow;
        }

        $html = view('reports.exports.' . $viewName, [
            'data' => $tableData,
            'headings' => $headings,
            'title' => $title,
            'date' => Carbon::now()->format('d/m/Y H:i'),
        ])->render();

        $dompdf = new Dompdf();
        $dompdf->getOptions()->set('isHtml5ParserEnabled', true);
        $dompdf->getOptions()->set('isRemoteEnabled', true);
        $dompdf->getOptions()->set('defaultFont', 'Arial');
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    // Exportações para KM por Veículo
    public function kmByVehicleExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $vehicles = Vehicle::where('active', true)->get();
        $data = [];

        foreach ($vehicles as $vehicle) {
            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);
            
            // Usar consumo real se disponível, senão usar consumo por período
            $consumption = $consumptionData['real_consumption'] ?? $consumptionData['period_consumption'];

            $data[] = [
                'Veículo' => $vehicle->name,
                'KM Total' => number_format($consumptionData['total_km'], 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $trips->count(),
                'Litros Abastecidos' => number_format($consumptionData['total_liters'], 2, ',', '.') . ' L',
                'Consumo Médio (km/L)' => $consumption ? number_format($consumption, 2, ',', '.') : '-',
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Veículo', 'KM Total', 'Quantidade de Percursos', 'Litros Abastecidos', 'Consumo Médio (km/L)'],
            'KM por Veículo',
            'km_por_veiculo_' . $startDate . '_' . $endDate
        );
    }

    public function kmByVehicleExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $vehicles = Vehicle::where('active', true)->get();
        $data = [];

        foreach ($vehicles as $vehicle) {
            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);
            
            // Usar consumo real se disponível, senão usar consumo por período
            $consumption = $consumptionData['real_consumption'] ?? $consumptionData['period_consumption'];

            $data[] = [
                'Veículo' => $vehicle->name,
                'KM Total' => number_format($consumptionData['total_km'], 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $trips->count(),
                'Litros Abastecidos' => number_format($consumptionData['total_liters'], 2, ',', '.') . ' L',
                'Consumo Médio (km/L)' => $consumption ? number_format($consumption, 2, ',', '.') : '-',
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Veículo', 'KM Total', 'Quantidade de Percursos', 'Litros Abastecidos', 'Consumo Médio (km/L)'],
            'Relatório - KM por Veículo',
            'table',
            'km_por_veiculo_' . $startDate . '_' . $endDate
        );
    }

    // Exportações para Consumo Médio
    public function consumoMedioExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $vehicles = Vehicle::where('active', true)->get();
        $data = [];

        foreach ($vehicles as $vehicle) {
            if ($vehicleId && $vehicle->id != $vehicleId) {
                continue;
            }

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);
            
            // Usar consumo real se disponível, senão usar consumo por período
            $consumption = $consumptionData['real_consumption'] ?? $consumptionData['period_consumption'];

            if ($consumptionData['total_km'] > 0 || $consumptionData['total_liters'] > 0) {
                $data[] = [
                    'Veículo' => $vehicle->name,
                    'KM Rodado' => number_format($consumptionData['total_km'], 0, ',', '.') . ' km',
                    'Litros Abastecidos' => number_format($consumptionData['total_liters'], 2, ',', '.') . ' L',
                    'Consumo Médio (km/L)' => $consumption ? number_format($consumption, 2, ',', '.') : '-',
                ];
            }
        }

        return $this->exportToExcel(
            $data,
            ['Veículo', 'KM Rodado', 'Litros Abastecidos', 'Consumo Médio (km/L)'],
            'Consumo Médio',
            'consumo_medio_' . $startDate . '_' . $endDate
        );
    }

    public function consumoMedioExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $vehicles = Vehicle::where('active', true)->get();
        $data = [];

        foreach ($vehicles as $vehicle) {
            if ($vehicleId && $vehicle->id != $vehicleId) {
                continue;
            }

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);
            
            // Usar consumo real se disponível, senão usar consumo por período
            $consumption = $consumptionData['real_consumption'] ?? $consumptionData['period_consumption'];

            if ($consumptionData['total_km'] > 0 || $consumptionData['total_liters'] > 0) {
                $data[] = [
                    'Veículo' => $vehicle->name,
                    'KM Rodado' => number_format($consumptionData['total_km'], 0, ',', '.') . ' km',
                    'Litros Abastecidos' => number_format($consumptionData['total_liters'], 2, ',', '.') . ' L',
                    'Consumo Médio (km/L)' => $consumption ? number_format($consumption, 2, ',', '.') : '-',
                ];
            }
        }

        return $this->exportToPDF(
            $data,
            ['Veículo', 'KM Rodado', 'Litros Abastecidos', 'Consumo Médio (km/L)'],
            'Relatório - Consumo Médio (KM/L) por Veículo',
            'table',
            'consumo_medio_' . $startDate . '_' . $endDate
        );
    }

    // ========== EXPORTAÇÕES EXCEL E PDF ==========
    
    // Exportação Fuel Cost
    public function fuelCostExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');
        $driverId = $request->input('driver_id');

        $query = Fueling::select('vehicle_id', 'user_id', DB::raw('SUM(total_amount) as total_cost'), DB::raw('SUM(liters) as total_liters'))
            ->whereBetween('date_time', [$startDate, $endDate])
            ->with(['vehicle', 'user']);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($driverId) {
            $query->where('user_id', $driverId);
        }

        $results = $query->groupBy('vehicle_id', 'user_id')->get();

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'Veículo' => $result->vehicle->name ?? '-',
                'Motorista' => $result->user->name ?? '-',
                'Litros' => number_format($result->total_liters, 2, ',', '.') . ' L',
                'Custo Total' => 'R$ ' . number_format($result->total_cost, 2, ',', '.'),
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Veículo', 'Motorista', 'Litros', 'Custo Total'],
            'Custo de Combustível',
            'custo_combustivel_' . $startDate . '_' . $endDate
        );
    }

    public function fuelCostExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');
        $driverId = $request->input('driver_id');

        $query = Fueling::select('vehicle_id', 'user_id', DB::raw('SUM(total_amount) as total_cost'), DB::raw('SUM(liters) as total_liters'))
            ->whereBetween('date_time', [$startDate, $endDate])
            ->with(['vehicle', 'user']);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($driverId) {
            $query->where('user_id', $driverId);
        }

        $results = $query->groupBy('vehicle_id', 'user_id')->get();

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'Veículo' => $result->vehicle->name ?? '-',
                'Motorista' => $result->user->name ?? '-',
                'Litros' => number_format($result->total_liters, 2, ',', '.') . ' L',
                'Custo Total' => 'R$ ' . number_format($result->total_cost, 2, ',', '.'),
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Veículo', 'Motorista', 'Litros', 'Custo Total'],
            'Relatório - Custo de Combustível',
            'table',
            'custo_combustivel_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Fuel Cost By Vehicle
    public function fuelCostByVehicleExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $results = Fueling::select('vehicle_id', DB::raw('SUM(total_amount) as total_cost'))
            ->whereBetween('date_time', [$startDate, $endDate])
            ->groupBy('vehicle_id')
            ->with('vehicle')
            ->get();

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'Veículo' => $result->vehicle->name ?? '-',
                'Custo Total' => 'R$ ' . number_format($result->total_cost, 2, ',', '.'),
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Veículo', 'Custo Total'],
            'Custo de Combustível por Veículo',
            'custo_combustivel_por_veiculo_' . $startDate . '_' . $endDate
        );
    }

    public function fuelCostByVehicleExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $results = Fueling::select('vehicle_id', DB::raw('SUM(total_amount) as total_cost'))
            ->whereBetween('date_time', [$startDate, $endDate])
            ->groupBy('vehicle_id')
            ->with('vehicle')
            ->get();

        $data = [];
        foreach ($results as $result) {
            $data[] = [
                'Veículo' => $result->vehicle->name ?? '-',
                'Custo Total' => 'R$ ' . number_format($result->total_cost, 2, ',', '.'),
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Veículo', 'Custo Total'],
            'Relatório - Custo de Combustível por Veículo',
            'table',
            'custo_combustivel_por_veiculo_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Fuelings
    public function fuelingsExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');
        $fuelType = $request->input('fuel_type');

        $query = Fueling::with(['vehicle', 'user'])
            ->whereBetween('date_time', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }

        $fuelings = $query->orderBy('date_time', 'desc')->get();

        $data = [];
        foreach ($fuelings as $fueling) {
            $data[] = [
                'Data/Hora' => $fueling->date_time->format('d/m/Y H:i'),
                'Veículo' => $fueling->vehicle->name ?? '-',
                'Tipo' => $fueling->fuel_type ?? '-',
                'Litros' => number_format($fueling->liters, 2, ',', '.') . ' L',
                'Preço/L' => 'R$ ' . number_format($fueling->price_per_liter, 2, ',', '.'),
                'Valor Total' => 'R$ ' . number_format($fueling->total_amount, 2, ',', '.'),
                'Posto' => $fueling->gas_station_name ?? '-',
                'Usuário' => $fueling->user->name ?? '-',
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Data/Hora', 'Veículo', 'Tipo', 'Litros', 'Preço/L', 'Valor Total', 'Posto', 'Usuário'],
            'Resumo de Abastecimentos',
            'abastecimentos_' . $startDate . '_' . $endDate
        );
    }

    public function fuelingsExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');
        $fuelType = $request->input('fuel_type');

        $query = Fueling::with(['vehicle', 'user'])
            ->whereBetween('date_time', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($fuelType) {
            $query->where('fuel_type', $fuelType);
        }

        $fuelings = $query->orderBy('date_time', 'desc')->get();

        $data = [];
        foreach ($fuelings as $fueling) {
            $data[] = [
                'Data/Hora' => $fueling->date_time->format('d/m/Y H:i'),
                'Veículo' => $fueling->vehicle->name ?? '-',
                'Tipo' => $fueling->fuel_type ?? '-',
                'Litros' => number_format($fueling->liters, 2, ',', '.') . ' L',
                'Preço/L' => 'R$ ' . number_format($fueling->price_per_liter, 2, ',', '.'),
                'Valor Total' => 'R$ ' . number_format($fueling->total_amount, 2, ',', '.'),
                'Posto' => $fueling->gas_station_name ?? '-',
                'Usuário' => $fueling->user->name ?? '-',
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Data/Hora', 'Veículo', 'Tipo', 'Litros', 'Preço/L', 'Valor Total', 'Posto', 'Usuário'],
            'Relatório - Resumo de Abastecimentos',
            'table',
            'abastecimentos_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Maintenances
    public function maintenancesExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with(['vehicle', 'user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        $data = [];
        foreach ($maintenances as $maintenance) {
            $data[] = [
                'Data' => $maintenance->date->format('d/m/Y'),
                'Veículo' => $maintenance->vehicle->name ?? '-',
                'Tipo' => ucfirst(str_replace('_', ' ', $maintenance->type)),
                'Custo' => $maintenance->cost ? 'R$ ' . number_format($maintenance->cost, 2, ',', '.') : '-',
                'KM' => number_format($maintenance->odometer, 0, ',', '.') . ' km',
                'Usuário' => $maintenance->user->name ?? '-',
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Data', 'Veículo', 'Tipo', 'Custo', 'KM', 'Usuário'],
            'Manutenções',
            'manutencoes_' . $startDate . '_' . $endDate
        );
    }

    public function maintenancesExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with(['vehicle', 'user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        $data = [];
        foreach ($maintenances as $maintenance) {
            $data[] = [
                'Data' => $maintenance->date->format('d/m/Y'),
                'Veículo' => $maintenance->vehicle->name ?? '-',
                'Tipo' => ucfirst(str_replace('_', ' ', $maintenance->type)),
                'Custo' => $maintenance->cost ? 'R$ ' . number_format($maintenance->cost, 2, ',', '.') : '-',
                'KM' => number_format($maintenance->odometer, 0, ',', '.') . ' km',
                'Usuário' => $maintenance->user->name ?? '-',
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Data', 'Veículo', 'Tipo', 'Custo', 'KM', 'Usuário'],
            'Relatório - Manutenções',
            'table',
            'manutencoes_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Maintenances Detailed
    public function maintenancesDetailedExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with(['vehicle', 'maintenanceType', 'user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->orderBy('date', 'desc')->get();

        $data = [];
        foreach ($maintenances as $maintenance) {
            $data[] = [
                'Data' => $maintenance->date->format('d/m/Y'),
                'Veículo' => $maintenance->vehicle->name ?? '-',
                'Tipo' => $maintenance->maintenanceType->name ?? ucfirst(str_replace('_', ' ', $maintenance->type)),
                'Custo' => $maintenance->cost ? 'R$ ' . number_format($maintenance->cost, 2, ',', '.') : '-',
                'KM' => number_format($maintenance->odometer, 0, ',', '.') . ' km',
                'Descrição' => $maintenance->description ?? '-',
                'Usuário' => $maintenance->user->name ?? '-',
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Data', 'Veículo', 'Tipo', 'Custo', 'KM', 'Descrição', 'Usuário'],
            'Manutenções Detalhado',
            'manutencoes_detalhado_' . $startDate . '_' . $endDate
        );
    }

    public function maintenancesDetailedExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with(['vehicle', 'maintenanceType', 'user'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->orderBy('date', 'desc')->get();

        $data = [];
        foreach ($maintenances as $maintenance) {
            $data[] = [
                'Data' => $maintenance->date->format('d/m/Y'),
                'Veículo' => $maintenance->vehicle->name ?? '-',
                'Tipo' => $maintenance->maintenanceType->name ?? ucfirst(str_replace('_', ' ', $maintenance->type)),
                'Custo' => $maintenance->cost ? 'R$ ' . number_format($maintenance->cost, 2, ',', '.') : '-',
                'KM' => number_format($maintenance->odometer, 0, ',', '.') . ' km',
                'Descrição' => $maintenance->description ?? '-',
                'Usuário' => $maintenance->user->name ?? '-',
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Data', 'Veículo', 'Tipo', 'Custo', 'KM', 'Descrição', 'Usuário'],
            'Relatório - Manutenções Detalhado',
            'table',
            'manutencoes_detalhado_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Driver Usage
    public function driverUsageExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $driverId = $request->input('driver_id');

        $query = Trip::select('driver_id', 
                DB::raw('SUM(km_total) as total_km'),
                DB::raw('COUNT(*) as trip_count'),
                DB::raw('AVG(km_total) as avg_km_per_trip'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('driver')
            ->groupBy('driver_id');

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        $results = $query->get();

        $daysInPeriod = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $monthsInPeriod = $daysInPeriod / 30;

        $data = [];
        foreach ($results as $result) {
            $avgKmPerMonth = $monthsInPeriod > 0 ? round($result->total_km / $monthsInPeriod, 2) : 0;
            $data[] = [
                'Condutor' => $result->driver->name ?? '-',
                'KM Total' => number_format($result->total_km, 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $result->trip_count,
                'KM Médio por Percurso' => number_format($result->avg_km_per_trip, 2, ',', '.') . ' km',
                'KM Médio por Mês' => number_format($avgKmPerMonth, 2, ',', '.') . ' km',
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Condutor', 'KM Total', 'Quantidade de Percursos', 'KM Médio por Percurso', 'KM Médio por Mês'],
            'Uso da Frota por Condutor',
            'uso_frota_condutor_' . $startDate . '_' . $endDate
        );
    }

    public function driverUsageExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $driverId = $request->input('driver_id');

        $query = Trip::select('driver_id', 
                DB::raw('SUM(km_total) as total_km'),
                DB::raw('COUNT(*) as trip_count'),
                DB::raw('AVG(km_total) as avg_km_per_trip'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('driver')
            ->groupBy('driver_id');

        if ($driverId) {
            $query->where('driver_id', $driverId);
        }

        $results = $query->get();

        $daysInPeriod = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $monthsInPeriod = $daysInPeriod / 30;

        $data = [];
        foreach ($results as $result) {
            $avgKmPerMonth = $monthsInPeriod > 0 ? round($result->total_km / $monthsInPeriod, 2) : 0;
            $data[] = [
                'Condutor' => $result->driver->name ?? '-',
                'KM Total' => number_format($result->total_km, 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $result->trip_count,
                'KM Médio por Percurso' => number_format($result->avg_km_per_trip, 2, ',', '.') . ' km',
                'KM Médio por Mês' => number_format($avgKmPerMonth, 2, ',', '.') . ' km',
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Condutor', 'KM Total', 'Quantidade de Percursos', 'KM Médio por Percurso', 'KM Médio por Mês'],
            'Relatório - Uso da Frota por Condutor',
            'table',
            'uso_frota_condutor_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Routes and Stops
    public function routesAndStopsExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Trip::with(['vehicle', 'originLocation', 'destinationLocation', 'stops.location'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $trips = $query->orderBy('date', 'desc')->get();

        // Rotas mais frequentes
        $routes = [];
        foreach ($trips as $trip) {
            $key = $trip->origin_location_id . '-' . $trip->destination_location_id;
            if (!isset($routes[$key])) {
                $routes[$key] = [
                    'origin' => $trip->originLocation,
                    'destination' => $trip->destinationLocation,
                    'count' => 0,
                    'total_km' => 0,
                ];
            }
            $routes[$key]['count']++;
            $routes[$key]['total_km'] += $trip->km_total;
        }

        $data = [];
        foreach ($routes as $route) {
            $data[] = [
                'Origem' => $route['origin']->name ?? '-',
                'Destino' => $route['destination']->name ?? '-',
                'Quantidade' => $route['count'],
                'KM Total' => number_format($route['total_km'], 0, ',', '.') . ' km',
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Origem', 'Destino', 'Quantidade', 'KM Total'],
            'Rotas Mais Frequentes',
            'rotas_paradas_' . $startDate . '_' . $endDate
        );
    }

    public function routesAndStopsExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $query = Trip::with(['vehicle', 'originLocation', 'destinationLocation', 'stops.location'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $trips = $query->orderBy('date', 'desc')->get();

        // Rotas mais frequentes
        $routes = [];
        foreach ($trips as $trip) {
            $key = $trip->origin_location_id . '-' . $trip->destination_location_id;
            if (!isset($routes[$key])) {
                $routes[$key] = [
                    'origin' => $trip->originLocation,
                    'destination' => $trip->destinationLocation,
                    'count' => 0,
                    'total_km' => 0,
                ];
            }
            $routes[$key]['count']++;
            $routes[$key]['total_km'] += $trip->km_total;
        }

        $data = [];
        foreach ($routes as $route) {
            $data[] = [
                'Origem' => $route['origin']->name ?? '-',
                'Destino' => $route['destination']->name ?? '-',
                'Quantidade' => $route['count'],
                'KM Total' => number_format($route['total_km'], 0, ',', '.') . ' km',
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Origem', 'Destino', 'Quantidade', 'KM Total'],
            'Relatório - Rotas Mais Frequentes',
            'table',
            'rotas_paradas_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Consolidated
    public function consolidatedExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $vehicles = Vehicle::where('active', true)->get();
        $data = [];

        foreach ($vehicles as $vehicle) {
            if ($vehicleId && $vehicle->id != $vehicleId) {
                continue;
            }

            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $maintenances = Maintenance::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);
            $totalFuelCost = $fuelings->sum('total_amount');
            $totalMaintenanceCost = $maintenances->sum('cost');
            $tco = $totalFuelCost + $totalMaintenanceCost;
            $consumption = $consumptionData['real_consumption'] ?? $consumptionData['period_consumption'];

            $data[] = [
                'Veículo' => $vehicle->name,
                'KM Rodado' => number_format($consumptionData['total_km'], 0, ',', '.') . ' km',
                'Percursos' => $trips->count(),
                'Litros' => number_format($consumptionData['total_liters'], 2, ',', '.') . ' L',
                'Consumo Médio' => $consumption ? number_format($consumption, 2, ',', '.') . ' km/L' : '-',
                'Custo Combustível' => 'R$ ' . number_format($totalFuelCost, 2, ',', '.'),
                'Manutenções' => $maintenances->count(),
                'Custo Manutenção' => 'R$ ' . number_format($totalMaintenanceCost, 2, ',', '.'),
                'TCO' => 'R$ ' . number_format($tco, 2, ',', '.'),
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Veículo', 'KM Rodado', 'Percursos', 'Litros', 'Consumo Médio', 'Custo Combustível', 'Manutenções', 'Custo Manutenção', 'TCO'],
            'Relatório Consolidado',
            'relatorio_consolidado_' . $startDate . '_' . $endDate
        );
    }

    public function consolidatedExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $vehicleId = $request->input('vehicle_id');

        $vehicles = Vehicle::where('active', true)->get();
        $data = [];

        foreach ($vehicles as $vehicle) {
            if ($vehicleId && $vehicle->id != $vehicleId) {
                continue;
            }

            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $maintenances = Maintenance::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $consumptionData = Fueling::calculateRealConsumption($vehicle->id, $startDate, $endDate);
            $totalFuelCost = $fuelings->sum('total_amount');
            $totalMaintenanceCost = $maintenances->sum('cost');
            $tco = $totalFuelCost + $totalMaintenanceCost;
            $consumption = $consumptionData['real_consumption'] ?? $consumptionData['period_consumption'];

            $data[] = [
                'Veículo' => $vehicle->name,
                'KM Rodado' => number_format($consumptionData['total_km'], 0, ',', '.') . ' km',
                'Percursos' => $trips->count(),
                'Litros' => number_format($consumptionData['total_liters'], 2, ',', '.') . ' L',
                'Consumo Médio' => $consumption ? number_format($consumption, 2, ',', '.') . ' km/L' : '-',
                'Custo Combustível' => 'R$ ' . number_format($totalFuelCost, 2, ',', '.'),
                'Manutenções' => $maintenances->count(),
                'Custo Manutenção' => 'R$ ' . number_format($totalMaintenanceCost, 2, ',', '.'),
                'TCO' => 'R$ ' . number_format($tco, 2, ',', '.'),
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Veículo', 'KM Rodado', 'Percursos', 'Litros', 'Consumo Médio', 'Custo Combustível', 'Manutenções', 'Custo Manutenção', 'TCO'],
            'Relatório Consolidado',
            'table',
            'relatorio_consolidado_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Ranking
    public function rankingExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $topDrivers = Trip::select('driver_id', DB::raw('SUM(km_total) as total_km'), DB::raw('COUNT(*) as trip_count'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('driver')
            ->groupBy('driver_id')
            ->orderBy('total_km', 'desc')
            ->limit(10)
            ->get();

        $data = [];
        foreach ($topDrivers as $driver) {
            $data[] = [
                'Condutor' => $driver->driver->name ?? '-',
                'KM Total' => number_format($driver->total_km, 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $driver->trip_count,
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Condutor', 'KM Total', 'Quantidade de Percursos'],
            'Ranking - Top Condutores',
            'ranking_condutores_' . $startDate . '_' . $endDate
        );
    }

    public function rankingExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $topDrivers = Trip::select('driver_id', DB::raw('SUM(km_total) as total_km'), DB::raw('COUNT(*) as trip_count'))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('driver')
            ->groupBy('driver_id')
            ->orderBy('total_km', 'desc')
            ->limit(10)
            ->get();

        $data = [];
        foreach ($topDrivers as $driver) {
            $data[] = [
                'Condutor' => $driver->driver->name ?? '-',
                'KM Total' => number_format($driver->total_km, 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $driver->trip_count,
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Condutor', 'KM Total', 'Quantidade de Percursos'],
            'Relatório - Ranking Top Condutores',
            'table',
            'ranking_condutores_' . $startDate . '_' . $endDate
        );
    }

    // Exportação Upcoming Maintenance
    public function upcomingMaintenanceExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with('vehicle')
            ->where(function($q) {
                $q->whereNotNull('next_due_date')
                  ->orWhereNotNull('next_due_odometer');
            });

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        $data = [];
        foreach ($maintenances as $maintenance) {
            $vehicle = $maintenance->vehicle;
            $currentKm = $vehicle->current_odometer ?? 0;
            $today = Carbon::today();
            
            $nextDueDate = $maintenance->next_due_date ? $maintenance->next_due_date->format('d/m/Y') : '-';
            $nextDueKm = $maintenance->next_due_odometer ? number_format($maintenance->next_due_odometer, 0, ',', '.') . ' km' : '-';
            
            $status = 'OK';
            if ($maintenance->next_due_date) {
                $daysUntil = $today->diffInDays($maintenance->next_due_date, false);
                if ($daysUntil < 0) {
                    $status = 'Atrasado (' . abs($daysUntil) . ' dias)';
                } elseif ($daysUntil <= 30) {
                    $status = 'Próximo (' . $daysUntil . ' dias)';
                }
            }
            
            if ($maintenance->next_due_odometer) {
                $kmUntil = $maintenance->next_due_odometer - $currentKm;
                if ($kmUntil < 0) {
                    $status = 'Atrasado (' . number_format(abs($kmUntil), 0, ',', '.') . ' km)';
                } elseif ($kmUntil <= 1000) {
                    $status = 'Próximo (' . number_format($kmUntil, 0, ',', '.') . ' km)';
                }
            }

            $data[] = [
                'Veículo' => $vehicle->name ?? '-',
                'Tipo' => ucfirst(str_replace('_', ' ', $maintenance->type)),
                'Próxima Data' => $nextDueDate,
                'Próximo KM' => $nextDueKm,
                'KM Atual' => number_format($currentKm, 0, ',', '.') . ' km',
                'Status' => $status,
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Veículo', 'Tipo', 'Próxima Data', 'Próximo KM', 'KM Atual', 'Status'],
            'Manutenções Futuras',
            'manutencoes_futuras'
        );
    }

    public function upcomingMaintenanceExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = Maintenance::with('vehicle')
            ->where(function($q) {
                $q->whereNotNull('next_due_date')
                  ->orWhereNotNull('next_due_odometer');
            });

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $maintenances = $query->get();

        $data = [];
        foreach ($maintenances as $maintenance) {
            $vehicle = $maintenance->vehicle;
            $currentKm = $vehicle->current_odometer ?? 0;
            $today = Carbon::today();
            
            $nextDueDate = $maintenance->next_due_date ? $maintenance->next_due_date->format('d/m/Y') : '-';
            $nextDueKm = $maintenance->next_due_odometer ? number_format($maintenance->next_due_odometer, 0, ',', '.') . ' km' : '-';
            
            $status = 'OK';
            if ($maintenance->next_due_date) {
                $daysUntil = $today->diffInDays($maintenance->next_due_date, false);
                if ($daysUntil < 0) {
                    $status = 'Atrasado (' . abs($daysUntil) . ' dias)';
                } elseif ($daysUntil <= 30) {
                    $status = 'Próximo (' . $daysUntil . ' dias)';
                }
            }
            
            if ($maintenance->next_due_odometer) {
                $kmUntil = $maintenance->next_due_odometer - $currentKm;
                if ($kmUntil < 0) {
                    $status = 'Atrasado (' . number_format(abs($kmUntil), 0, ',', '.') . ' km)';
                } elseif ($kmUntil <= 1000) {
                    $status = 'Próximo (' . number_format($kmUntil, 0, ',', '.') . ' km)';
                }
            }

            $data[] = [
                'Veículo' => $vehicle->name ?? '-',
                'Tipo' => ucfirst(str_replace('_', ' ', $maintenance->type)),
                'Próxima Data' => $nextDueDate,
                'Próximo KM' => $nextDueKm,
                'KM Atual' => number_format($currentKm, 0, ',', '.') . ' km',
                'Status' => $status,
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Veículo', 'Tipo', 'Próxima Data', 'Próximo KM', 'KM Atual', 'Status'],
            'Relatório - Manutenções Futuras',
            'table',
            'manutencoes_futuras'
        );
    }

    // Exportação Odometer Audit
    public function odometerAuditExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = Trip::with('vehicle');

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $trips = $query->orderBy('vehicle_id')->orderBy('date')->orderBy('odometer_start')->get();

        $data = [];
        $previousTrip = null;

        foreach ($trips as $trip) {
            $issues = [];

            if ($previousTrip && $previousTrip->vehicle_id == $trip->vehicle_id) {
                if ($trip->odometer_start < $previousTrip->odometer_end) {
                    $issues[] = 'KM regressivo detectado';
                }

                $expectedKm = $trip->odometer_start - $previousTrip->odometer_end;
                if ($expectedKm < 0) {
                    $issues[] = 'KM inicial menor que KM final anterior';
                } elseif ($expectedKm > 10000) {
                    $issues[] = 'Gap suspeito: ' . number_format($expectedKm, 0, ',', '.') . ' km';
                }
            }

            if ($trip->odometer_start > $trip->odometer_end) {
                $issues[] = 'Odômetro inicial maior que final';
            }

            if (!empty($issues) || !$vehicleId) {
                $data[] = [
                    'Data' => $trip->date->format('d/m/Y'),
                    'Veículo' => $trip->vehicle->name ?? '-',
                    'KM Inicial' => number_format($trip->odometer_start, 0, ',', '.') . ' km',
                    'KM Final' => number_format($trip->odometer_end, 0, ',', '.') . ' km',
                    'KM Rodado' => number_format($trip->km_total, 0, ',', '.') . ' km',
                    'Problemas' => !empty($issues) ? implode('; ', $issues) : 'OK',
                ];
            }

            $previousTrip = $trip;
        }

        return $this->exportToExcel(
            $data,
            ['Data', 'Veículo', 'KM Inicial', 'KM Final', 'KM Rodado', 'Problemas'],
            'Auditoria de Odômetro',
            'auditoria_odometro'
        );
    }

    public function odometerAuditExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = Trip::with('vehicle');

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $trips = $query->orderBy('vehicle_id')->orderBy('date')->orderBy('odometer_start')->get();

        $data = [];
        $previousTrip = null;

        foreach ($trips as $trip) {
            $issues = [];

            if ($previousTrip && $previousTrip->vehicle_id == $trip->vehicle_id) {
                if ($trip->odometer_start < $previousTrip->odometer_end) {
                    $issues[] = 'KM regressivo detectado';
                }

                $expectedKm = $trip->odometer_start - $previousTrip->odometer_end;
                if ($expectedKm < 0) {
                    $issues[] = 'KM inicial menor que KM final anterior';
                } elseif ($expectedKm > 10000) {
                    $issues[] = 'Gap suspeito: ' . number_format($expectedKm, 0, ',', '.') . ' km';
                }
            }

            if ($trip->odometer_start > $trip->odometer_end) {
                $issues[] = 'Odômetro inicial maior que final';
            }

            if (!empty($issues) || !$vehicleId) {
                $data[] = [
                    'Data' => $trip->date->format('d/m/Y'),
                    'Veículo' => $trip->vehicle->name ?? '-',
                    'KM Inicial' => number_format($trip->odometer_start, 0, ',', '.') . ' km',
                    'KM Final' => number_format($trip->odometer_end, 0, ',', '.') . ' km',
                    'KM Rodado' => number_format($trip->km_total, 0, ',', '.') . ' km',
                    'Problemas' => !empty($issues) ? implode('; ', $issues) : 'OK',
                ];
            }

            $previousTrip = $trip;
        }

        return $this->exportToPDF(
            $data,
            ['Data', 'Veículo', 'KM Inicial', 'KM Final', 'KM Rodado', 'Problemas'],
            'Relatório - Auditoria de Odômetro',
            'table',
            'auditoria_odometro'
        );
    }

    // Exportação Reviews
    public function reviewsExportExcel(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = ReviewNotification::with('vehicle')
            ->where('active', true);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $reviews = $query->get();

        $data = [];
        foreach ($reviews as $review) {
            $vehicle = $review->vehicle;
            $currentKm = $vehicle->current_odometer ?? 0;
            $kmUntil = $review->notification_km - $currentKm;
            $status = 'OK';
            if ($kmUntil < 0) {
                $status = 'Atrasado';
            } elseif ($kmUntil <= 500) {
                $status = 'Atenção';
            }

            $data[] = [
                'Veículo' => $vehicle->name ?? '-',
                'Tipo de Revisão' => $review->name ?: $review->review_type_name,
                'KM Atual' => number_format($currentKm, 0, ',', '.') . ' km',
                'KM Notificação' => number_format($review->notification_km, 0, ',', '.') . ' km',
                'KM Restantes' => number_format($kmUntil, 0, ',', '.') . ' km',
                'Status' => $status,
            ];
        }

        return $this->exportToExcel(
            $data,
            ['Veículo', 'Tipo de Revisão', 'KM Atual', 'KM Notificação', 'KM Restantes', 'Status'],
            'Controle de Revisões',
            'controle_revisoes'
        );
    }

    public function reviewsExportPdf(Request $request)
    {
        $this->checkReportsPermission();
        
        $vehicleId = $request->input('vehicle_id');

        $query = ReviewNotification::with('vehicle')
            ->where('active', true);

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $reviews = $query->get();

        $data = [];
        foreach ($reviews as $review) {
            $vehicle = $review->vehicle;
            $currentKm = $vehicle->current_odometer ?? 0;
            $kmUntil = $review->notification_km - $currentKm;
            $status = 'OK';
            if ($kmUntil < 0) {
                $status = 'Atrasado';
            } elseif ($kmUntil <= 500) {
                $status = 'Atenção';
            }

            $data[] = [
                'Veículo' => $vehicle->name ?? '-',
                'Tipo de Revisão' => $review->name ?: $review->review_type_name,
                'KM Atual' => number_format($currentKm, 0, ',', '.') . ' km',
                'KM Notificação' => number_format($review->notification_km, 0, ',', '.') . ' km',
                'KM Restantes' => number_format($kmUntil, 0, ',', '.') . ' km',
                'Status' => $status,
            ];
        }

        return $this->exportToPDF(
            $data,
            ['Veículo', 'Tipo de Revisão', 'KM Atual', 'KM Notificação', 'KM Restantes', 'Status'],
            'Relatório - Controle de Revisões',
            'table',
            'controle_revisoes'
        );
    }

    /**
     * Formata um valor de time para o formato H:i:s válido
     * Remove zeros extras e garante formato correto
     */
    private function formatTime($time)
    {
        if (empty($time)) {
            return null;
        }

        // Se já for uma string, limpar e formatar
        $timeString = (string) $time;
        
        // Remover espaços
        $timeString = trim($timeString);
        
        // Se estiver vazio, retornar null
        if (empty($timeString)) {
            return null;
        }

        // Tentar diferentes formatos
        // Formato esperado: H:i:s ou H:i
        // Remover zeros extras no final (ex: 12:30:000 -> 12:30:00)
        $timeString = preg_replace('/:0+$/', '', $timeString); // Remove :000, :00 no final
        $timeString = preg_replace('/:0+:/', ':', $timeString); // Remove zeros extras no meio
        
        // Validar formato básico (deve ter pelo menos H:i)
        if (!preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeString)) {
            return null;
        }

        // Garantir formato H:i:s (adicionar :00 se necessário)
        $parts = explode(':', $timeString);
        if (count($parts) === 2) {
            $timeString .= ':00';
        } elseif (count($parts) === 3) {
            // Garantir que os segundos tenham 2 dígitos
            $parts[2] = str_pad($parts[2], 2, '0', STR_PAD_LEFT);
            $timeString = implode(':', $parts);
        }

        // Validar valores (hora 0-23, minuto 0-59, segundo 0-59)
        $parts = explode(':', $timeString);
        if (count($parts) === 3) {
            $hour = (int) $parts[0];
            $minute = (int) $parts[1];
            $second = (int) $parts[2];
            
            if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59) {
                return null;
            }
        }

        return $timeString;
    }
}
