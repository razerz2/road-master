<?php

namespace App\Http\Controllers;

use App\Models\Fueling;
use App\Models\Vehicle;
use App\Models\PaymentMethod;
use App\Models\FuelType;
use App\Models\GasStation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class FuelingController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Fueling::class);

        $query = Fueling::with(['vehicle', 'user']);

        // Filtros
        if ($request->filled('start_date')) {
            $query->where('date_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date_time', '<=', $request->end_date);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Condutor só vê seus próprios abastecimentos
        if (Auth::user()->role === 'condutor') {
            $query->where('user_id', Auth::id());
        }

        $fuelings = $query->latest('date_time')->paginate(20);
        $vehicles = Vehicle::where('active', true)->get();

        return view('fuelings.index', compact('fuelings', 'vehicles'));
    }

    public function create()
    {
        Gate::authorize('create', Fueling::class);

        $user = Auth::user();
        $vehicles = Vehicle::where('active', true)->get();
        $paymentMethods = PaymentMethod::where('active', true)->orderBy('order')->orderBy('name')->get();
        $fuelTypes = FuelType::where('active', true)->orderBy('order')->orderBy('name')->get();
        $gasStations = GasStation::where('active', true)->orderBy('order')->orderBy('name')->get();
        
        // Incluir condutores e admins na lista (incluir 'motorista' para compatibilidade com dados antigos)
        $drivers = User::whereIn('role', ['condutor', 'motorista', 'admin'])->where('active', true)->get();
        
        // Garantir que o admin logado esteja sempre na lista
        if ($user->role === 'admin' && !$drivers->contains('id', $user->id)) {
            $drivers->push($user);
        }

        return view('fuelings.create', compact('vehicles', 'paymentMethods', 'fuelTypes', 'gasStations', 'drivers'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Fueling::class);

        $user = Auth::user();
        
        $validationRules = [
            'vehicle_id' => 'required|exists:vehicles,id',
            'date_time' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'fuel_type' => 'required|string|max:255',
            'liters' => 'required|numeric|min:0',
            'price_per_liter' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'gas_station_name' => 'nullable|string|max:255',
            'gas_station_id' => 'nullable|exists:gas_stations,id',
            'payment_method' => 'nullable|string|max:255',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ];
        
        // Admin pode escolher o usuário, condutor sempre usa seu próprio ID
        if ($user->role === 'admin') {
            $validationRules['user_id'] = 'required|exists:users,id';
        }
        
        $validated = $request->validate($validationRules);

        // Calcular total_amount se não informado
        if (empty($validated['total_amount'])) {
            $validated['total_amount'] = $validated['liters'] * $validated['price_per_liter'];
        }

        // Se for condutor, usar o próprio ID
        if ($user->role === 'condutor') {
            $validated['user_id'] = $user->id;
        } elseif ($user->role !== 'admin') {
            // Outros usuários também usam seu próprio ID
            $validated['user_id'] = $user->id;
        }

        $fueling = Fueling::create($validated);

        // Atualizar odômetro do veículo se necessário
        $vehicle = Vehicle::find($validated['vehicle_id']);
        if ($vehicle && $validated['odometer'] > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $validated['odometer']]);
        }

        return redirect()->route('fuelings.index')
            ->with('success', 'Abastecimento registrado com sucesso!');
    }

    public function show(Fueling $fueling)
    {
        Gate::authorize('view', $fueling);

        $fueling->load(['vehicle', 'user']);

        return view('fuelings.show', compact('fueling'));
    }

    public function edit(Fueling $fueling)
    {
        Gate::authorize('update', $fueling);

        $user = Auth::user();
        $vehicles = Vehicle::where('active', true)->get();
        $paymentMethods = PaymentMethod::where('active', true)->orderBy('order')->orderBy('name')->get();
        $fuelTypes = FuelType::where('active', true)->orderBy('order')->orderBy('name')->get();
        $gasStations = GasStation::where('active', true)->orderBy('order')->orderBy('name')->get();
        $fueling->load('paymentMethod', 'gasStation', 'user');
        
        // Incluir condutores e admins na lista (incluir 'motorista' para compatibilidade com dados antigos)
        $drivers = User::whereIn('role', ['condutor', 'motorista', 'admin'])->where('active', true)->get();
        
        // Garantir que o admin logado esteja sempre na lista
        if ($user->role === 'admin' && !$drivers->contains('id', $user->id)) {
            $drivers->push($user);
        }

        return view('fuelings.edit', compact('fueling', 'vehicles', 'paymentMethods', 'fuelTypes', 'gasStations', 'drivers'));
    }

    public function update(Request $request, Fueling $fueling)
    {
        Gate::authorize('update', $fueling);

        $user = Auth::user();
        
        $validationRules = [
            'vehicle_id' => 'required|exists:vehicles,id',
            'date_time' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'fuel_type' => 'required|string|max:255',
            'liters' => 'required|numeric|min:0',
            'price_per_liter' => 'required|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'gas_station_name' => 'nullable|string|max:255',
            'gas_station_id' => 'nullable|exists:gas_stations,id',
            'payment_method' => 'nullable|string|max:255',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'notes' => 'nullable|string',
        ];
        
        // Admin pode escolher o usuário, condutor sempre usa seu próprio ID
        if ($user->role === 'admin') {
            $validationRules['user_id'] = 'required|exists:users,id';
        }
        
        $validated = $request->validate($validationRules);

        // Calcular total_amount se não informado
        if (empty($validated['total_amount'])) {
            $validated['total_amount'] = $validated['liters'] * $validated['price_per_liter'];
        }

        // Se for condutor, usar o próprio ID
        if ($user->role === 'condutor') {
            $validated['user_id'] = $user->id;
        } elseif ($user->role !== 'admin') {
            // Outros usuários também usam seu próprio ID
            $validated['user_id'] = $user->id;
        }

        $fueling->update($validated);

        // Atualizar odômetro do veículo se necessário
        $vehicle = Vehicle::find($validated['vehicle_id']);
        if ($vehicle && $validated['odometer'] > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $validated['odometer']]);
        }

        return redirect()->route('fuelings.index')
            ->with('success', 'Abastecimento atualizado com sucesso!');
    }

    public function destroy(Fueling $fueling)
    {
        Gate::authorize('delete', $fueling);

        $fueling->delete();

        return redirect()->route('fuelings.index')
            ->with('success', 'Abastecimento removido com sucesso!');
    }
}
