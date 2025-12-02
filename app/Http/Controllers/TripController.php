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
        
        // Incluir condutores e admins na lista (incluir 'motorista' para compatibilidade com dados antigos)
        $drivers = User::whereIn('role', ['condutor', 'motorista', 'admin'])->where('active', true)->get();

        return view('trips.index', compact('trips', 'vehicles', 'drivers'));
    }

    public function create()
    {
        Gate::authorize('create', Trip::class);

        $user = Auth::user();
        
        // Listar apenas veículos relacionados para condutores
        if ($user->role === 'admin') {
            $vehicles = Vehicle::where('active', true)->get();
        } elseif ($user->role === 'condutor') {
            $vehicles = $user->vehicles()->where('active', true)->get();
        } else {
            $vehicles = Vehicle::where('active', true)->get();
        }
        
        $locations = Location::all();
        // Incluir condutores e admins na lista (incluir 'motorista' para compatibilidade com dados antigos)
        $drivers = User::whereIn('role', ['condutor', 'motorista', 'admin'])->where('active', true)->get();
        
        // Garantir que o admin logado esteja sempre na lista
        if ($user->role === 'admin' && !$drivers->contains('id', $user->id)) {
            $drivers->push($user);
        }

        return view('trips.create', compact('vehicles', 'locations', 'drivers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Trip::class);

        $user = Auth::user();
        
        // Se for condutor, usar o próprio ID como driver_id
        $validationRules = [
            'vehicle_id' => 'required|exists:vehicles,id',
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
        ];
        
        // Admin pode escolher o condutor, condutor sempre usa seu próprio ID
        if ($user->role === 'admin') {
            $validationRules['driver_id'] = 'required|exists:users,id';
        }
        
        $validated = $request->validate($validationRules);
        
        // Se for condutor, usar o próprio ID
        if ($user->role === 'condutor') {
            $validated['driver_id'] = $user->id;
        }

        // Verificar se o condutor tem relação com o veículo (admin sempre pode)
        if ($user->role === 'condutor') {
            $hasVehicleRelation = $user->vehicles()->where('vehicles.id', $validated['vehicle_id'])->exists();
            if (!$hasVehicleRelation) {
                return back()->withErrors(['vehicle_id' => 'Você não tem permissão para usar este veículo.'])->withInput();
            }
        }

        // Validar KM inicial e final
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $currentOdometer = $vehicle->current_odometer ?? 0;
        
        // Validar se KM inicial corresponde ao odômetro atual do veículo
        if ($validated['odometer_start'] != $currentOdometer) {
            return back()->withErrors(['odometer_start' => "O KM de saída deve ser igual ao odômetro atual do veículo ({$currentOdometer} km)."])->withInput();
        }
        
        // Validar se KM final é maior que KM inicial (já validado acima, mas garantindo)
        if ($validated['odometer_end'] <= $validated['odometer_start']) {
            return back()->withErrors(['odometer_end' => 'O KM de chegada deve ser maior que o KM de saída.'])->withInput();
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

            // Atualizar odômetro do veículo sempre
            $this->updateVehicleOdometer($validated['vehicle_id']);
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
        
        // Listar apenas veículos relacionados para condutores
        if ($user->role === 'admin') {
            $vehicles = Vehicle::where('active', true)->get();
        } elseif ($user->role === 'condutor') {
            $vehicles = $user->vehicles()->where('active', true)->get();
        } else {
            $vehicles = Vehicle::where('active', true)->get();
        }
        
        $locations = Location::all();
        // Incluir condutores e admins na lista (incluir 'motorista' para compatibilidade com dados antigos)
        $drivers = User::whereIn('role', ['condutor', 'motorista', 'admin'])->where('active', true)->get();
        
        $trip->load(['stops' => function($query) {
            $query->orderBy('sequence');
        }, 'stops.location']);

        return view('trips.edit', compact('trip', 'vehicles', 'locations', 'drivers'));
    }

    public function update(Request $request, Trip $trip)
    {
        Gate::authorize('update', $trip);

        $user = Auth::user();
        
        // Se for condutor, usar o próprio ID como driver_id
        $validationRules = [
            'vehicle_id' => 'required|exists:vehicles,id',
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
        ];
        
        // Admin pode escolher o condutor, condutor sempre usa seu próprio ID
        if ($user->role === 'admin') {
            $validationRules['driver_id'] = 'required|exists:users,id';
        }
        
        $validated = $request->validate($validationRules);
        
        // Se for condutor, usar o próprio ID
        if ($user->role === 'condutor') {
            $validated['driver_id'] = $user->id;
        }

        // Verificar se o condutor tem relação com o veículo (admin sempre pode)
        if ($user->role === 'condutor') {
            $hasVehicleRelation = $user->vehicles()->where('vehicles.id', $validated['vehicle_id'])->exists();
            if (!$hasVehicleRelation) {
                return back()->withErrors(['vehicle_id' => 'Você não tem permissão para usar este veículo.'])->withInput();
            }
        }

        // Validar KM inicial e final
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $currentOdometer = $vehicle->current_odometer ?? 0;
        
        // Para edição, verificar se o KM inicial corresponde ao odômetro atual do veículo
        // (exceto se for o mesmo percurso sendo editado, nesse caso usar o KM inicial original)
        $originalOdometerStart = $trip->odometer_start;
        $expectedOdometerStart = $currentOdometer;
        
        // Se o veículo foi alterado ou se o KM inicial foi modificado, validar
        if ($validated['vehicle_id'] != $trip->vehicle_id || $validated['odometer_start'] != $originalOdometerStart) {
            if ($validated['odometer_start'] != $expectedOdometerStart) {
                return back()->withErrors(['odometer_start' => "O KM de saída deve ser igual ao odômetro atual do veículo ({$expectedOdometerStart} km)."])->withInput();
            }
        }
        
        // Validar se KM final é maior que KM inicial
        if ($validated['odometer_end'] <= $validated['odometer_start']) {
            return back()->withErrors(['odometer_end' => 'O KM de chegada deve ser maior que o KM de saída.'])->withInput();
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

            // Atualizar odômetro do veículo sempre
            // Se o veículo foi alterado, atualizar ambos os veículos
            if ($validated['vehicle_id'] != $trip->vehicle_id) {
                $this->updateVehicleOdometer($trip->vehicle_id); // Veículo antigo
            }
            $this->updateVehicleOdometer($validated['vehicle_id']); // Veículo novo (ou mesmo)
        });

        return redirect()->route('trips.index')
            ->with('success', 'Percurso atualizado com sucesso!');
    }

    public function destroy(Trip $trip)
    {
        Gate::authorize('delete', $trip);

        $vehicleId = $trip->vehicle_id;
        
        DB::transaction(function () use ($trip) {
            $trip->delete();
        });

        // Atualizar odômetro do veículo após exclusão
        $this->updateVehicleOdometer($vehicleId);

        return redirect()->route('trips.index')
            ->with('success', 'Percurso removido com sucesso!');
    }

    /**
     * Atualiza o odômetro do veículo baseado no maior odometer_end dos percursos
     * Garante que o odômetro nunca seja menor que o km_inicial do veículo
     */
    private function updateVehicleOdometer($vehicleId)
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return;
        }

        // Buscar o maior odometer_end dos percursos do veículo
        $maxOdometer = Trip::where('vehicle_id', $vehicleId)
            ->max('odometer_end');

        // Se não houver percursos, usar km_inicial ou manter current_odometer
        if ($maxOdometer === null) {
            $maxOdometer = $vehicle->km_inicial ?? $vehicle->current_odometer ?? 0;
        }

        // Garantir que o odômetro nunca seja menor que o km_inicial
        $kmInicial = $vehicle->km_inicial ?? 0;
        if ($maxOdometer < $kmInicial) {
            $maxOdometer = $kmInicial;
        }

        // Atualizar o odômetro do veículo
        $vehicle->update(['current_odometer' => $maxOdometer]);
    }

    public function getVehicleOdometer(Request $request, $vehicleId)
    {
        Gate::authorize('create', Trip::class);

        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($vehicleId);
        
        // Verificar se o condutor tem relação com o veículo (admin sempre pode)
        if ($user->role === 'condutor') {
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
