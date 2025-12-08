<?php

namespace App\Http\Controllers;

use App\Models\GasStation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class GasStationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class); // Apenas admin pode acessar
        
        $gasStations = GasStation::orderBy('order')->orderBy('name')->paginate(20);
        
        $gasStation = null;
        $editingId = null;
        
        if ($request->has('edit')) {
            $gasStation = GasStation::findOrFail($request->edit);
            $editingId = $gasStation->id;
        }
        
        return view('gas-stations.index', compact('gasStations', 'gasStation', 'editingId'));
    }

    public function store(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gas_stations,name',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        GasStation::create($validated);

        return redirect()->route('gas-stations.index')
            ->with('success', 'Posto criado com sucesso!');
    }

    public function update(Request $request, GasStation $gasStation)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gas_stations,name,' . $gasStation->id,
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        $gasStation->update($validated);

        return redirect()->route('gas-stations.index')
            ->with('success', 'Posto atualizado com sucesso!');
    }

    public function destroy(GasStation $gasStation)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        // Verificar se há abastecimentos associados
        if ($gasStation->fuelings()->count() > 0) {
            return redirect()->route('gas-stations.index')
                ->with('error', 'Não é possível excluir o posto pois existem abastecimentos associados a ele.');
        }

        $gasStation->delete();

        return redirect()->route('gas-stations.index')
            ->with('success', 'Posto excluído com sucesso!');
    }
}
