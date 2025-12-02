<?php

namespace App\Http\Controllers;

use App\Models\FuelType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class FuelTypeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class); // Apenas admin pode acessar
        
        $fuelTypes = FuelType::orderBy('order')->orderBy('name')->get();
        
        $fuelType = null;
        $editingId = null;
        
        if ($request->has('edit')) {
            $fuelType = FuelType::findOrFail($request->edit);
            $editingId = $fuelType->id;
        }
        
        return view('fuel-types.index', compact('fuelTypes', 'fuelType', 'editingId'));
    }

    public function store(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fuel_types,name',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        FuelType::create($validated);

        return redirect()->route('fuel-types.index')
            ->with('success', 'Tipo de combustível criado com sucesso!');
    }

    public function update(Request $request, FuelType $fuelType)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fuel_types,name,' . $fuelType->id,
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        $fuelType->update($validated);

        return redirect()->route('fuel-types.index')
            ->with('success', 'Tipo de combustível atualizado com sucesso!');
    }

    public function destroy(FuelType $fuelType)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        // Verificar se há veículos associados
        if ($fuelType->vehicles()->count() > 0) {
            return redirect()->route('fuel-types.index')
                ->with('error', 'Não é possível excluir o tipo de combustível pois existem veículos associados a ele.');
        }

        $fuelType->delete();

        return redirect()->route('fuel-types.index')
            ->with('success', 'Tipo de combustível excluído com sucesso!');
    }
}
