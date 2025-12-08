<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\LocationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query();
        
        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }
        
        $locations = $query->orderBy('name')->paginate(20)->withQueryString();
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        $locationTypes = LocationType::where('active', true)->orderBy('order')->orderBy('name')->get();
        return view('locations.create', compact('locationTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:empresa,cliente,posto_combustivel,outro',
            'location_type_id' => 'nullable|exists:location_types,id',
            'address' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'notes' => 'nullable|string',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Local cadastrado com sucesso!');
    }

    public function storeAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:empresa,cliente,posto_combustivel,outro',
                'location_type_id' => 'nullable|exists:location_types,id',
                'address' => 'nullable|string',
                'street' => 'nullable|string|max:255',
                'number' => 'nullable|string|max:20',
                'complement' => 'nullable|string|max:255',
                'neighborhood' => 'nullable|string|max:255',
                'zip_code' => 'nullable|string|max:10',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:2',
                'notes' => 'nullable|string',
            ]);

            $location = Location::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Local cadastrado com sucesso!',
                'location' => [
                    'id' => $location->id,
                    'name' => $location->name,
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
                'message' => 'Erro ao cadastrar local: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Location $location)
    {
        return view('locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        $locationTypes = LocationType::where('active', true)->orderBy('order')->orderBy('name')->get();
        $location->load('locationType');
        return view('locations.edit', compact('location', 'locationTypes'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:empresa,cliente,posto_combustivel,outro',
            'location_type_id' => 'nullable|exists:location_types,id',
            'address' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'notes' => 'nullable|string',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Local atualizado com sucesso!');
    }

    public function destroy(Location $location)
    {
        try {
            // Verificar se existem viagens que usam este local como origem
            $originTripsCount = $location->originTrips()->count();
            
            // Verificar se existem viagens que usam este local como destino
            $destinationTripsCount = $location->destinationTrips()->count();
            
            // Verificar se existem paradas intermediárias que usam este local
            $tripStopsCount = $location->tripStops()->count();
            
            // Se existir qualquer dependência, impedir a exclusão
            if ($originTripsCount > 0 || $destinationTripsCount > 0 || $tripStopsCount > 0) {
                $totalUsages = $originTripsCount + $destinationTripsCount + $tripStopsCount;
                
                $message = "Não é possível excluir este local pois ele está sendo utilizado em ";
                
                $usageDetails = [];
                if ($originTripsCount > 0) {
                    $usageDetails[] = "{$originTripsCount} viagem(ns) como origem";
                }
                if ($destinationTripsCount > 0) {
                    $usageDetails[] = "{$destinationTripsCount} viagem(ns) como destino";
                }
                if ($tripStopsCount > 0) {
                    $usageDetails[] = "{$tripStopsCount} parada(s) intermediária(s)";
                }
                
                $message .= implode(", ", $usageDetails) . ".";
                
                return redirect()->route('locations.index')
                    ->with('error', $message);
            }
            
            // Se não houver dependências, permitir a exclusão
            $location->delete();

            return redirect()->route('locations.index')
                ->with('success', 'Local removido com sucesso!');
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar erro de integridade referencial caso ainda ocorra
            if ($e->getCode() == '23000') {
                return redirect()->route('locations.index')
                    ->with('error', 'Não é possível excluir este local pois ele está sendo utilizado em outras partes do sistema.');
            }
            
            // Re-lançar exceção se for outro tipo de erro
            throw $e;
        }
    }
}
