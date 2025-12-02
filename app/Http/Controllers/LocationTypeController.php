<?php

namespace App\Http\Controllers;

use App\Models\LocationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class LocationTypeController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class); // Apenas admin pode acessar
        
        $locationTypes = LocationType::orderBy('order')->orderBy('name')->get();
        
        $locationType = null;
        $editingId = null;
        
        if ($request->has('edit')) {
            $locationType = LocationType::findOrFail($request->edit);
            $editingId = $locationType->id;
        }
        
        return view('location-types.index', compact('locationTypes', 'locationType', 'editingId'));
    }

    public function store(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:location_types,name',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        LocationType::create($validated);

        return redirect()->route('location-types.index')
            ->with('success', 'Tipo de local criado com sucesso!');
    }

    public function update(Request $request, LocationType $locationType)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:location_types,name,' . $locationType->id,
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        $locationType->update($validated);

        return redirect()->route('location-types.index')
            ->with('success', 'Tipo de local atualizado com sucesso!');
    }

    public function destroy(LocationType $locationType)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        // Verificar se há locais associados
        if ($locationType->locations()->count() > 0) {
            return redirect()->route('location-types.index')
                ->with('error', 'Não é possível excluir o tipo de local pois existem locais associados a ele.');
        }

        $locationType->delete();

        return redirect()->route('location-types.index')
            ->with('success', 'Tipo de local excluído com sucesso!');
    }
}
