<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\LocationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('name')->get();
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
        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Local removido com sucesso!');
    }
}
