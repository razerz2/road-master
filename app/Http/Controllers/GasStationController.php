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

    public function storeAjax(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);
        
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:gas_stations,name'
                ],
                'description' => 'nullable|string|max:1000',
                'active' => 'boolean',
                'order' => 'nullable|integer|min:0',
            ], [
                'name.required' => 'O nome do posto é obrigatório.',
                'name.unique' => 'Já existe um posto cadastrado com este nome.',
                'name.max' => 'O nome do posto não pode ter mais de 255 caracteres.',
            ]);

            $validated['slug'] = Str::slug($validated['name']);
            $validated['active'] = $request->has('active') ? true : true; // Sempre ativo por padrão
            $validated['order'] = $validated['order'] ?? 0;

            $gasStation = GasStation::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Posto cadastrado com sucesso!',
                'gasStation' => [
                    'id' => $gasStation->id,
                    'name' => $gasStation->name,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar posto: ' . $e->getMessage()
            ], 500);
        }
    }
}
