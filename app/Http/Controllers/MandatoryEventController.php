<?php

namespace App\Http\Controllers;

use App\Models\VehicleMandatoryEvent;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MandatoryEventController extends Controller
{
    /**
     * Listar todas as obrigações legais
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', VehicleMandatoryEvent::class);

        $query = VehicleMandatoryEvent::with('vehicle');

        // Filtros
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('resolved')) {
            $query->where('resolved', $request->resolved === '1');
        }

        $events = $query->latest('due_date')->paginate(20);
        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();
        $types = VehicleMandatoryEvent::getTypes();

        return view('mandatory-events.index', compact('events', 'vehicles', 'types'));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        Gate::authorize('create', VehicleMandatoryEvent::class);

        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();
        $types = VehicleMandatoryEvent::getTypes();

        return view('mandatory-events.create', compact('vehicles', 'types'));
    }

    /**
     * Criar nova obrigação legal
     */
    public function store(Request $request)
    {
        Gate::authorize('create', VehicleMandatoryEvent::class);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:licenciamento,ipva,multa',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'recurring' => 'nullable|boolean',
        ]);

        // Garantir que recurring seja boolean
        $validated['recurring'] = $request->has('recurring') && $request->recurring == '1';

        VehicleMandatoryEvent::create($validated);

        return redirect()->route('mandatory-events.index')
            ->with('success', 'Obrigação legal criada com sucesso!');
    }

    /**
     * Mostrar uma obrigação específica
     */
    public function show(VehicleMandatoryEvent $mandatoryEvent)
    {
        Gate::authorize('view', $mandatoryEvent);

        $mandatoryEvent->load('vehicle');

        return view('mandatory-events.show', compact('mandatoryEvent'));
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit(VehicleMandatoryEvent $mandatoryEvent)
    {
        Gate::authorize('update', $mandatoryEvent);

        // Não permitir edição se já estiver marcada como paga
        if ($mandatoryEvent->resolved) {
            return redirect()->route('mandatory-events.show', $mandatoryEvent)
                ->with('error', 'Não é possível editar uma obrigatoriedade que já foi marcada como paga.');
        }

        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();
        $types = VehicleMandatoryEvent::getTypes();

        return view('mandatory-events.edit', compact('mandatoryEvent', 'vehicles', 'types'));
    }

    /**
     * Atualizar obrigação legal
     */
    public function update(Request $request, VehicleMandatoryEvent $mandatoryEvent)
    {
        Gate::authorize('update', $mandatoryEvent);

        // Não permitir edição se já estiver marcada como paga
        if ($mandatoryEvent->resolved) {
            return redirect()->route('mandatory-events.show', $mandatoryEvent)
                ->with('error', 'Não é possível editar uma obrigatoriedade que já foi marcada como paga.');
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:licenciamento,ipva,multa',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'recurring' => 'nullable|boolean',
        ]);

        // Garantir que recurring seja boolean
        $validated['recurring'] = $request->has('recurring') && $request->recurring == '1';

        $mandatoryEvent->update($validated);

        return redirect()->route('mandatory-events.index')
            ->with('success', 'Obrigação legal atualizada com sucesso!');
    }

    /**
     * Remover obrigação legal
     */
    public function destroy(VehicleMandatoryEvent $mandatoryEvent)
    {
        Gate::authorize('delete', $mandatoryEvent);

        $mandatoryEvent->delete();

        return redirect()->route('mandatory-events.index')
            ->with('success', 'Obrigação legal removida com sucesso!');
    }

    /**
     * Marcar como resolvido (pago)
     */
    public function markResolved(VehicleMandatoryEvent $mandatoryEvent)
    {
        Gate::authorize('update', $mandatoryEvent);

        // Criar próxima ocorrência recorrente ANTES de marcar como resolvida
        // (para IPVA e Licenciamento)
        $nextEvent = $mandatoryEvent->createNextRecurrence();

        // Marcar como resolvida
        $mandatoryEvent->update([
            'resolved' => true,
        ]);

        $message = 'Obrigação legal marcada como paga!';
        if ($nextEvent) {
            $message .= ' Próxima ocorrência criada automaticamente para ' . $nextEvent->due_date->format('d/m/Y') . '.';
        }

        return redirect()->back()
            ->with('success', $message);
    }
}
