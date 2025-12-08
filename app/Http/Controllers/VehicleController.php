<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\FuelType;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Vehicle::class);
        
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            $vehicles = Vehicle::with('fuelTypes')->orderBy('name')->paginate(20);
        } elseif ($user->role === 'condutor') {
            // Condutor só vê veículos que tem relação
            $vehicles = $user->vehicles()->with('fuelTypes')->orderBy('name')->paginate(20);
        } else {
            $vehicles = Vehicle::with('fuelTypes')->orderBy('name')->paginate(20);
        }
        
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        Gate::authorize('create', Vehicle::class);
        $fuelTypes = FuelType::where('active', true)->orderBy('order')->orderBy('name')->get();
        return view('vehicles.create', compact('fuelTypes'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Vehicle::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate' => 'required|string|max:10|unique:vehicles,plate',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'fuel_type_id' => 'nullable|exists:fuel_types,id',
            'tank_capacity' => 'nullable|numeric|min:0',
            'current_odometer' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        $fuelTypeId = $validated['fuel_type_id'] ?? null;
        unset($validated['fuel_type_id']);

        $vehicle = Vehicle::create($validated);
        // Sincronizar apenas o tipo selecionado (ou array vazio se nenhum foi selecionado)
        $vehicle->fuelTypes()->sync($fuelTypeId ? [$fuelTypeId] : []);

        return redirect()->route('vehicles.index')
            ->with('success', 'Veículo cadastrado com sucesso!');
    }

    public function show(Vehicle $vehicle)
    {
        Gate::authorize('view', $vehicle);

        $user = Auth::user();

        $vehicle->load([
            'fuelTypes',
            'trips' => function($query) use ($user) {
                // Filtrar percursos que o condutor pode ver
                if ($user->role === 'admin') {
                    $query->latest()->limit(10);
                } elseif ($user->role === 'condutor') {
                    $query->where('driver_id', $user->id)->latest()->limit(10);
                } else {
                    $query->latest()->limit(10);
                }
            }, 
            'fuelings' => function($query) {
                $query->latest()->limit(10);
            }, 
            'maintenances' => function($query) {
                $query->latest()->limit(10);
            }
        ]);

        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        Gate::authorize('update', $vehicle);
        $fuelTypes = FuelType::where('active', true)->orderBy('order')->orderBy('name')->get();
        $vehicle->load('fuelTypes');
        return view('vehicles.edit', compact('vehicle', 'fuelTypes'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        Gate::authorize('update', $vehicle);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate' => 'required|string|max:10|unique:vehicles,plate,' . $vehicle->id,
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'fuel_type_id' => 'nullable|exists:fuel_types,id',
            'tank_capacity' => 'nullable|numeric|min:0',
            'current_odometer' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ]);

        $fuelTypeId = $validated['fuel_type_id'] ?? null;
        unset($validated['fuel_type_id']);

        $vehicle->update($validated);
        // Sincronizar apenas o tipo selecionado (ou array vazio se nenhum foi selecionado)
        $vehicle->fuelTypes()->sync($fuelTypeId ? [$fuelTypeId] : []);

        return redirect()->route('vehicles.index')
            ->with('success', 'Veículo atualizado com sucesso!');
    }

    public function destroy(Vehicle $vehicle)
    {
        Gate::authorize('delete', $vehicle);
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Veículo removido com sucesso!');
    }

    /**
     * Ajustar KM do veículo baseado em todos os percursos registrados
     * Verifica todos os percursos do primeiro ao último e atualiza o odômetro
     */
    public function adjustOdometer(Vehicle $vehicle)
    {
        Gate::authorize('update', $vehicle);

        // Buscar todos os percursos do veículo ordenados por data e hora
        $trips = $vehicle->trips()
            ->orderBy('date')
            ->orderBy('departure_time')
            ->get();

        $oldOdometer = $vehicle->current_odometer ?? 0;
        $kmInicial = $vehicle->km_inicial ?? 0;

        if ($trips->isEmpty()) {
            // Se não houver percursos, usar km_inicial ou manter current_odometer
            $newOdometer = $kmInicial > 0 ? $kmInicial : $oldOdometer;
        } else {
            // Buscar o maior odometer_end dos percursos
            $maxOdometer = $trips->max('odometer_end');

            // Se não houver odometer_end válido, usar km_inicial ou manter current_odometer
            if ($maxOdometer === null || $maxOdometer <= 0) {
                $newOdometer = $kmInicial > 0 ? $kmInicial : $oldOdometer;
            } else {
                // Garantir que o odômetro nunca seja menor que o km_inicial
                $newOdometer = max($maxOdometer, $kmInicial);
            }
        }

        // Atualizar o odômetro do veículo
        $vehicle->update(['current_odometer' => $newOdometer]);

        $difference = $newOdometer - $oldOdometer;
        $message = "KM do veículo ajustado com sucesso! ";
        
        if ($difference != 0) {
            $message .= sprintf(
                "Odômetro atualizado de %s km para %s km (%s%d km).",
                number_format($oldOdometer, 0, ',', '.'),
                number_format($newOdometer, 0, ',', '.'),
                $difference > 0 ? '+' : '',
                $difference
            );
        } else {
            $message .= "Odômetro já estava correto (" . number_format($newOdometer, 0, ',', '.') . " km).";
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', $message);
    }
}
