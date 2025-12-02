<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class MaintenanceTypeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class); // Apenas admin pode acessar
        
        $maintenanceTypes = MaintenanceType::orderBy('order')->orderBy('name')->get();
        
        $maintenanceType = null;
        $editingId = null;
        
        if ($request->has('edit')) {
            $maintenanceType = MaintenanceType::findOrFail($request->edit);
            $editingId = $maintenanceType->id;
        }
        
        return view('maintenance-types.index', compact('maintenanceTypes', 'maintenanceType', 'editingId'));
    }

    public function store(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_types,name',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        MaintenanceType::create($validated);

        return redirect()->route('maintenance-types.index')
            ->with('success', 'Tipo de manutenção criado com sucesso!');
    }

    public function update(Request $request, MaintenanceType $maintenanceType)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:maintenance_types,name,' . $maintenanceType->id,
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        $maintenanceType->update($validated);

        return redirect()->route('maintenance-types.index')
            ->with('success', 'Tipo de manutenção atualizado com sucesso!');
    }

    public function destroy(MaintenanceType $maintenanceType)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        // Verificar se há manutenções associadas
        if ($maintenanceType->maintenances()->count() > 0) {
            return redirect()->route('maintenance-types.index')
                ->with('error', 'Não é possível excluir o tipo de manutenção pois existem manutenções associadas a ele.');
        }

        $maintenanceType->delete();

        return redirect()->route('maintenance-types.index')
            ->with('success', 'Tipo de manutenção excluído com sucesso!');
    }
}
