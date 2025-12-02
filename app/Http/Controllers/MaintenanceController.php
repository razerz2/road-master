<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\MaintenanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Maintenance::class);

        $query = Maintenance::with('vehicle');

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
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $maintenances = $query->latest('date')->paginate(20);
        $vehicles = Vehicle::where('active', true)->get();

        return view('maintenances.index', compact('maintenances', 'vehicles'));
    }

    public function create()
    {
        Gate::authorize('create', Maintenance::class);

        $vehicles = Vehicle::where('active', true)->get();
        $maintenanceTypes = MaintenanceType::where('active', true)->orderBy('order')->orderBy('name')->get();

        return view('maintenances.create', compact('vehicles', 'maintenanceTypes'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Maintenance::class);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'type' => 'required|in:troca_oleo,revisao,pneu,freio,suspensao,outro',
            'maintenance_type_id' => 'nullable|exists:maintenance_types,id',
            'description' => 'required|string',
            'provider' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'next_due_date' => 'nullable|date',
            'next_due_odometer' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        Maintenance::create($validated);

        return redirect()->route('maintenances.index')
            ->with('success', 'Manutenção registrada com sucesso!');
    }

    public function show(Maintenance $maintenance)
    {
        Gate::authorize('view', $maintenance);

        $maintenance->load('vehicle');

        return view('maintenances.show', compact('maintenance'));
    }

    public function edit(Maintenance $maintenance)
    {
        Gate::authorize('update', $maintenance);

        $vehicles = Vehicle::where('active', true)->get();
        $maintenanceTypes = MaintenanceType::where('active', true)->orderBy('order')->orderBy('name')->get();
        $maintenance->load('maintenanceType');

        return view('maintenances.edit', compact('maintenance', 'vehicles', 'maintenanceTypes'));
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        Gate::authorize('update', $maintenance);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'type' => 'required|in:troca_oleo,revisao,pneu,freio,suspensao,outro',
            'maintenance_type_id' => 'nullable|exists:maintenance_types,id',
            'description' => 'required|string',
            'provider' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'next_due_date' => 'nullable|date',
            'next_due_odometer' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $maintenance->update($validated);

        return redirect()->route('maintenances.index')
            ->with('success', 'Manutenção atualizada com sucesso!');
    }

    public function destroy(Maintenance $maintenance)
    {
        Gate::authorize('delete', $maintenance);

        $maintenance->delete();

        return redirect()->route('maintenances.index')
            ->with('success', 'Manutenção removida com sucesso!');
    }
}
