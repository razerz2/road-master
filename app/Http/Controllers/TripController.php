<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripStop;
use App\Models\Vehicle;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Trip::class);

        $query = Trip::with(['vehicle', 'driver', 'originLocation', 'destinationLocation']);
        
        $user = Auth::user();

        // Filtros
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        
        // Condutor só vê seus próprios percursos de veículos relacionados a ele
        if ($user->role === 'condutor') {
            $userVehicleIds = $user->vehicles()->pluck('vehicles.id');
            $query->where('driver_id', $user->id)
                  ->whereIn('vehicle_id', $userVehicleIds);
        }

        $trips = $query->latest('date')->paginate(20);
        
        // Filtrar veículos para o filtro: condutores só veem veículos relacionados
        if ($user->role === 'admin') {
            $vehicles = Vehicle::where('active', true)->get();
        } elseif ($user->role === 'condutor') {
            $vehicles = $user->vehicles()->where('active', true)->get();
        } else {
            $vehicles = Vehicle::where('active', true)->get();
        }
        
        $drivers = User::where('role', 'condutor')->where('active', true)->get();

        return view('trips.index', compact('trips', 'vehicles', 'drivers'));
    }

    public function create()
    {
        Gate::authorize('create', Trip::class);

        $user = Auth::user();
        
        // Listar apenas veículos relacionados para motoristas
        if ($user->role === 'admin') {
            $vehicles = Vehicle::where('active', true)->get();
        } elseif ($user->role === 'motorista') {
            $vehicles = $user->vehicles()->where('active', true)->get();
        } else {
            $vehicles = Vehicle::where('active', true)->get();
        }
        
        $locations = Location::all();
        $drivers = User::where('role', 'condutor')->where('active', true)->get();

        return view('trips.create', compact('vehicles', 'locations', 'drivers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Trip::class);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'return_to_origin' => 'boolean',
            'departure_time' => 'required',
            'return_time' => 'nullable',
            'odometer_start' => 'required|integer|min:0',
            'odometer_end' => 'required|integer|gt:odometer_start',
            'purpose' => 'nullable|string',
            'stops' => 'nullable|array',
            'stops.*.location_id' => 'required_with:stops|exists:locations,id',
        ]);

        // Verificar se o condutor tem relação com o veículo (admin sempre pode)
        $user = Auth::user();
        if ($user->role === 'condutor') {
            $hasVehicleRelation = $user->vehicles()->where('vehicles.id', $validated['vehicle_id'])->exists();
            if (!$hasVehicleRelation) {
                return back()->withErrors(['vehicle_id' => 'Você não tem permissão para usar este veículo.'])->withInput();
            }
        }

        // Calcular km_total
        $validated['km_total'] = $validated['odometer_end'] - $validated['odometer_start'];
        $validated['created_by'] = Auth::id();

        DB::transaction(function () use ($validated, $request) {
            $trip = Trip::create($validated);

            // Salvar paradas intermediárias se houver
            if ($request->has('stops') && is_array($request->stops)) {
                $sequence = 1;
                foreach ($request->stops as $stopData) {
                    if (!empty($stopData['location_id'])) {
                        TripStop::create([
                            'trip_id' => $trip->id,
                            'location_id' => $stopData['location_id'],
                            'sequence' => $sequence++,
                        ]);
                    }
                }
            }

            // Atualizar odômetro do veículo se necessário
            $vehicle = Vehicle::find($validated['vehicle_id']);
            if ($vehicle && $validated['odometer_end'] > $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $validated['odometer_end']]);
            }
        });

        return redirect()->route('trips.index')
            ->with('success', 'Percurso registrado com sucesso!');
    }

    public function show(Trip $trip)
    {
        Gate::authorize('view', $trip);

        $trip->load(['vehicle', 'driver', 'originLocation', 'destinationLocation', 'creator', 'stops.location']);

        return view('trips.show', compact('trip'));
    }

    public function edit(Trip $trip)
    {
        Gate::authorize('update', $trip);

        $user = Auth::user();
        
        // Listar apenas veículos relacionados para motoristas
        if ($user->role === 'admin') {
            $vehicles = Vehicle::where('active', true)->get();
        } elseif ($user->role === 'motorista') {
            $vehicles = $user->vehicles()->where('active', true)->get();
        } else {
            $vehicles = Vehicle::where('active', true)->get();
        }
        
        $locations = Location::all();
        $drivers = User::where('role', 'condutor')->where('active', true)->get();
        
        $trip->load(['stops' => function($query) {
            $query->orderBy('sequence');
        }, 'stops.location']);

        return view('trips.edit', compact('trip', 'vehicles', 'locations', 'drivers'));
    }

    public function update(Request $request, Trip $trip)
    {
        Gate::authorize('update', $trip);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'origin_location_id' => 'required|exists:locations,id',
            'destination_location_id' => 'required|exists:locations,id',
            'return_to_origin' => 'boolean',
            'departure_time' => 'required',
            'return_time' => 'nullable',
            'odometer_start' => 'required|integer|min:0',
            'odometer_end' => 'required|integer|gt:odometer_start',
            'purpose' => 'nullable|string',
            'stops' => 'nullable|array',
            'stops.*.location_id' => 'required_with:stops|exists:locations,id',
        ]);

        // Verificar se o condutor tem relação com o veículo (admin sempre pode)
        $user = Auth::user();
        if ($user->role === 'condutor') {
            $hasVehicleRelation = $user->vehicles()->where('vehicles.id', $validated['vehicle_id'])->exists();
            if (!$hasVehicleRelation) {
                return back()->withErrors(['vehicle_id' => 'Você não tem permissão para usar este veículo.'])->withInput();
            }
        }

        // Calcular km_total
        $validated['km_total'] = $validated['odometer_end'] - $validated['odometer_start'];

        DB::transaction(function () use ($validated, $request, $trip) {
            $trip->update($validated);

            // Remover paradas antigas
            $trip->stops()->delete();

            // Salvar novas paradas intermediárias se houver
            if ($request->has('stops') && is_array($request->stops)) {
                $sequence = 1;
                foreach ($request->stops as $stopData) {
                    if (!empty($stopData['location_id'])) {
                        TripStop::create([
                            'trip_id' => $trip->id,
                            'location_id' => $stopData['location_id'],
                            'sequence' => $sequence++,
                        ]);
                    }
                }
            }

            // Atualizar odômetro do veículo se necessário
            $vehicle = Vehicle::find($validated['vehicle_id']);
            if ($vehicle && $validated['odometer_end'] > $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $validated['odometer_end']]);
            }
        });

        return redirect()->route('trips.index')
            ->with('success', 'Percurso atualizado com sucesso!');
    }

    public function destroy(Trip $trip)
    {
        Gate::authorize('delete', $trip);

        $trip->delete();

        return redirect()->route('trips.index')
            ->with('success', 'Percurso removido com sucesso!');
    }

    public function getVehicleOdometer(Request $request, $vehicleId)
    {
        Gate::authorize('create', Trip::class);

        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        // Verificar se o motorista tem relação com o veículo (admin sempre pode)
        if ($user->role === 'motorista') {
            $hasVehicleRelation = $user->vehicles()->where('vehicles.id', $vehicleId)->exists();
            if (!$hasVehicleRelation) {
                abort(403, 'Você não tem permissão para acessar este veículo.');
            }
        }
        
        return response()->json([
            'current_odometer' => $vehicle->current_odometer ?? 0
        ]);
    }
}
