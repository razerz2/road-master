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

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $totalKm = $trips->sum('km_total');
            $totalLiters = $fuelings->sum('liters');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;

            $results[] = [
                'vehicle' => $vehicle,
                'total_km' => $totalKm,
                'trip_count' => $trips->count(),
                'total_liters' => $totalLiters,
                'avg_consumption' => $avgConsumption,
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

        $query = Maintenance::with('vehicle')
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

            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $totalKm = $trips->sum('km_total');
            $totalLiters = $fuelings->sum('liters');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;

            if ($totalKm > 0 || $totalLiters > 0) {
                $results[] = [
                    'vehicle' => $vehicle,
                    'total_km' => $totalKm,
                    'total_liters' => $totalLiters,
                    'avg_consumption' => $avgConsumption,
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

        $query = Maintenance::with(['vehicle', 'maintenanceType'])
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
                $departure = Carbon::parse($trip->date->format('Y-m-d') . ' ' . $trip->departure_time);
                $return = Carbon::parse($trip->date->format('Y-m-d') . ' ' . $trip->return_time);
                $routes[$key]['avg_time'] += $departure->diffInMinutes($return);
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

            $totalKm = $trips->sum('km_total');
            $totalLiters = $fuelings->sum('liters');
            $totalFuelCost = $fuelings->sum('total_amount');
            $totalMaintenanceCost = $maintenances->sum('cost');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;
            $tco = $totalFuelCost + $totalMaintenanceCost;

            $results[] = [
                'vehicle' => $vehicle,
                'total_km' => $totalKm,
                'trip_count' => $trips->count(),
                'total_liters' => $totalLiters,
                'avg_consumption' => $avgConsumption,
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

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $totalKm = $trips->sum('km_total');
            $totalLiters = $fuelings->sum('liters');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;

            $data[] = [
                'Veículo' => $vehicle->name,
                'KM Total' => number_format($totalKm, 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $trips->count(),
                'Litros Abastecidos' => number_format($totalLiters, 2, ',', '.') . ' L',
                'Consumo Médio (km/L)' => $avgConsumption > 0 ? number_format($avgConsumption, 2, ',', '.') : '-',
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

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $totalKm = $trips->sum('km_total');
            $totalLiters = $fuelings->sum('liters');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;

            $data[] = [
                'Veículo' => $vehicle->name,
                'KM Total' => number_format($totalKm, 0, ',', '.') . ' km',
                'Quantidade de Percursos' => $trips->count(),
                'Litros Abastecidos' => number_format($totalLiters, 2, ',', '.') . ' L',
                'Consumo Médio (km/L)' => $avgConsumption > 0 ? number_format($avgConsumption, 2, ',', '.') : '-',
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

            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $totalKm = $trips->sum('km_total');
            $totalLiters = $fuelings->sum('liters');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;

            if ($totalKm > 0 || $totalLiters > 0) {
                $data[] = [
                    'Veículo' => $vehicle->name,
                    'KM Rodado' => number_format($totalKm, 0, ',', '.') . ' km',
                    'Litros Abastecidos' => number_format($totalLiters, 2, ',', '.') . ' L',
                    'Consumo Médio (km/L)' => number_format($avgConsumption, 2, ',', '.'),
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

            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $fuelings = Fueling::where('vehicle_id', $vehicle->id)
                ->whereBetween('date_time', [$startDate, $endDate])
                ->get();

            $totalKm = $trips->sum('km_total');
            $totalLiters = $fuelings->sum('liters');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;

            if ($totalKm > 0 || $totalLiters > 0) {
                $data[] = [
                    'Veículo' => $vehicle->name,
                    'KM Rodado' => number_format($totalKm, 0, ',', '.') . ' km',
                    'Litros Abastecidos' => number_format($totalLiters, 2, ',', '.') . ' L',
                    'Consumo Médio (km/L)' => number_format($avgConsumption, 2, ',', '.'),
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
}
