<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\Fueling;
use App\Models\VehicleMandatoryEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Carregar preferências do usuário
        $userPreferences = $user->preferences ?? [];
        
        // Usar valores da requisição ou preferências salvas, ou padrão
        $startDate = $request->input('start_date', 
            $userPreferences['dashboard_start_date'] ?? Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', 
            $userPreferences['dashboard_end_date'] ?? Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Tratar vehicle_id: se vier vazio na requisição, usar null
        $requestVehicleId = $request->input('vehicle_id');
        $vehicleId = !empty($requestVehicleId) ? $requestVehicleId : 
            ($userPreferences['dashboard_vehicle_id'] ?? null);

        // Total de veículos ativos
        if ($user->role === 'admin') {
            $totalVehicles = Vehicle::where('active', true)->count();
        } elseif ($user->role === 'condutor') {
            $totalVehicles = $user->vehicles()->where('active', true)->count();
        } else {
            $totalVehicles = Vehicle::where('active', true)->count();
        }

        // KM total rodado no período
        $query = Trip::whereBetween('date', [$startDate, $endDate]);
        
        // Aplicar filtros de permissão apenas para condutores
        if ($user->role === 'condutor') {
            $userVehicleIds = $user->vehicles()->pluck('vehicles.id');
            $query->where('driver_id', $user->id)
                  ->whereIn('vehicle_id', $userVehicleIds);
        }
        
        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }
        
        $totalKm = $query->sum('km_total');

        // Litros abastecidos no período
        $fuelingQuery = Fueling::whereBetween('date_time', [$startDate, $endDate]);
        if ($vehicleId) {
            $fuelingQuery->where('vehicle_id', $vehicleId);
        } elseif ($user->role === 'condutor') {
            // Filtrar abastecimentos apenas de veículos relacionados para condutores
            $userVehicleIds = $user->vehicles()->pluck('vehicles.id');
            $fuelingQuery->whereIn('vehicle_id', $userVehicleIds);
        }
        
        $totalLiters = $fuelingQuery->sum('liters');

        // Custo de combustível no período
        $totalCost = $fuelingQuery->sum('total_amount');

        // KM por veículo no período
        $kmByVehicleQuery = Trip::select('vehicle_id', DB::raw('SUM(km_total) as total_km'))
            ->whereBetween('date', [$startDate, $endDate]);
            
        if ($user->role === 'condutor') {
            $userVehicleIds = $user->vehicles()->pluck('vehicles.id');
            $kmByVehicleQuery->where('driver_id', $user->id)
                             ->whereIn('vehicle_id', $userVehicleIds);
        }
        
        $kmByVehicle = $kmByVehicleQuery->groupBy('vehicle_id')
            ->with('vehicle')
            ->get();

        // Filtrar veículos para o filtro: apenas condutores têm restrição
        if ($user->role === 'admin') {
            $vehicles = Vehicle::where('active', true)->get();
        } elseif ($user->role === 'condutor') {
            $vehicles = $user->vehicles()->where('active', true)->get();
        } else {
            $vehicles = Vehicle::where('active', true)->get();
        }

        // Obrigações legais próximas do vencimento
        $nextEventsQuery = VehicleMandatoryEvent::where('resolved', false)
            ->whereDate('due_date', '<=', now()->addDays(15))
            ->with('vehicle')
            ->orderBy('due_date');

        // Filtrar por veículos do condutor se necessário
        if ($user->role === 'condutor') {
            $userVehicleIds = $user->vehicles()->pluck('vehicles.id');
            $nextEventsQuery->whereIn('vehicle_id', $userVehicleIds);
        }

        $nextEvents = $nextEventsQuery->take(5)->get();

        // Contadores por tipo
        $baseQuery = VehicleMandatoryEvent::where('resolved', false)
            ->whereDate('due_date', '<=', now()->addDays(15));
        
        if ($user->role === 'condutor') {
            $userVehicleIds = $user->vehicles()->pluck('vehicles.id');
            $baseQuery->whereIn('vehicle_id', $userVehicleIds);
        }

        $totalUpcoming = (clone $baseQuery)->count();
        $totalIpva = (clone $baseQuery)->where('type', 'ipva')->count();
        $totalLicenciamento = (clone $baseQuery)->where('type', 'licenciamento')->count();
        $totalMultas = (clone $baseQuery)->where('type', 'multa')->count();

        return view('dashboard', compact(
            'totalVehicles',
            'totalKm',
            'totalLiters',
            'totalCost',
            'kmByVehicle',
            'vehicles',
            'startDate',
            'endDate',
            'vehicleId',
            'nextEvents',
            'totalUpcoming',
            'totalIpva',
            'totalLicenciamento',
            'totalMultas'
        ));
    }
}
