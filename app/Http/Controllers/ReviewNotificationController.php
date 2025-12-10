<?php

namespace App\Http\Controllers;

use App\Models\ReviewNotification;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewNotificationController extends Controller
{
    /**
     * Listar todas as revisões
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', ReviewNotification::class);

        $query = ReviewNotification::with('vehicle');

        // Filtros
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('review_type')) {
            $query->where('review_type', $request->review_type);
        }
        if ($request->filled('active')) {
            $query->where('active', $request->active === '1');
        }

        $notifications = $query->latest()->paginate(20);
        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();
        $reviewTypes = ReviewNotification::getReviewTypes();

        return view('review-notifications.index', compact('notifications', 'vehicles', 'reviewTypes'));
    }

    /**
     * Mostrar formulário de criação
     */
    public function create()
    {
        Gate::authorize('create', ReviewNotification::class);

        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();
        $reviewTypes = ReviewNotification::getReviewTypes();

        return view('review-notifications.create', compact('vehicles', 'reviewTypes'));
    }

    /**
     * Criar nova revisão
     */
    public function store(Request $request)
    {
        Gate::authorize('create', ReviewNotification::class);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'review_type' => 'required|string',
            'name' => 'nullable|string|max:255',
            'current_km' => 'nullable|integer|min:0',
            'notification_km' => 'required|integer|min:0',
            'active' => 'boolean',
            'description' => 'nullable|string',
            'recurring' => 'nullable|boolean',
            'recurrence_interval_km' => 'nullable|integer|min:1',
        ]);

        // Se current_km não foi informado, usar o odômetro atual do veículo
        if (empty($validated['current_km'])) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $validated['current_km'] = $vehicle->current_odometer ?? 0;
        }

        // Garantir que active seja boolean
        // Se o checkbox não estiver marcado, não vem no request, então precisa tratar explicitamente
        $validated['active'] = $request->has('active') && $request->active == '1';
        
        // Garantir que recurring seja boolean
        $validated['recurring'] = $request->has('recurring') && $request->recurring == '1';
        
        // Se recurring está marcado mas não tem intervalo, usar a diferença entre notification_km e current_km
        if ($validated['recurring'] && empty($validated['recurrence_interval_km'])) {
            $validated['recurrence_interval_km'] = $validated['notification_km'] - $validated['current_km'];
        }

        ReviewNotification::create($validated);

        return redirect()->route('review-notifications.index')
            ->with('success', 'Revisão criada com sucesso!');
    }

    /**
     * Mostrar uma notificação específica
     */
    public function show(ReviewNotification $reviewNotification)
    {
        Gate::authorize('view', $reviewNotification);

        $reviewNotification->load('vehicle');

        return view('review-notifications.show', compact('reviewNotification'));
    }

    /**
     * Mostrar formulário de edição
     */
    public function edit(ReviewNotification $reviewNotification)
    {
        Gate::authorize('update', $reviewNotification);

        $vehicles = Vehicle::where('active', true)->orderBy('name')->get();
        $reviewTypes = ReviewNotification::getReviewTypes();

        return view('review-notifications.edit', compact('reviewNotification', 'vehicles', 'reviewTypes'));
    }

    /**
     * Atualizar revisão
     */
    public function update(Request $request, ReviewNotification $reviewNotification)
    {
        Gate::authorize('update', $reviewNotification);

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'review_type' => 'required|string',
            'name' => 'nullable|string|max:255',
            'current_km' => 'nullable|integer|min:0',
            'notification_km' => 'required|integer|min:0',
            'active' => 'boolean',
            'description' => 'nullable|string',
            'recurring' => 'nullable|boolean',
            'recurrence_interval_km' => 'nullable|integer|min:1',
        ]);

        // Se current_km não foi informado, usar o odômetro atual do veículo
        if (empty($validated['current_km'])) {
            $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
            $validated['current_km'] = $vehicle->current_odometer ?? 0;
        }

        // Garantir que active seja boolean
        // Se o checkbox não estiver marcado, não vem no request, então precisa tratar explicitamente
        $validated['active'] = $request->has('active') && $request->active == '1';

        // Garantir que recurring seja boolean
        $validated['recurring'] = $request->has('recurring') && $request->recurring == '1';
        
        // Se recurring está marcado mas não tem intervalo, usar a diferença entre notification_km e current_km
        if ($validated['recurring'] && empty($validated['recurrence_interval_km'])) {
            $validated['recurrence_interval_km'] = $validated['notification_km'] - $validated['current_km'];
        }

        $reviewNotification->update($validated);

        return redirect()->route('review-notifications.index')
            ->with('success', 'Revisão atualizada com sucesso!');
    }

    /**
     * Remover revisão
     */
    public function destroy(ReviewNotification $reviewNotification)
    {
        Gate::authorize('delete', $reviewNotification);

        $reviewNotification->delete();

        return redirect()->route('review-notifications.index')
            ->with('success', 'Revisão removida com sucesso!');
    }

    /**
     * Ativar/Desativar notificação
     */
    public function toggleActive(ReviewNotification $reviewNotification)
    {
        Gate::authorize('update', $reviewNotification);

        $reviewNotification->update([
            'active' => !$reviewNotification->active,
        ]);

        return redirect()->back()
            ->with('success', $reviewNotification->active 
                ? 'Revisão ativada com sucesso!' 
                : 'Revisão desativada com sucesso!');
    }

    /**
     * Marcar revisão como realizada
     */
    public function markAsCompleted(Request $request, ReviewNotification $reviewNotification)
    {
        Gate::authorize('update', $reviewNotification);

        $validated = $request->validate([
            'completed_km' => 'nullable|integer|min:0',
        ]);

        $completedKm = $validated['completed_km'] ?? $reviewNotification->vehicle->current_odometer ?? null;
        
        // Criar próxima ocorrência recorrente ANTES de marcar como completa
        // Passa o completedKm para calcular corretamente o próximo KM
        $nextReview = $reviewNotification->createNextRecurrence($completedKm);
        
        // Marcar como completa
        $reviewNotification->markAsCompleted($completedKm);

        $message = 'Revisão marcada como realizada com sucesso!';
        if ($nextReview) {
            $message .= ' Próxima revisão criada automaticamente para ' . number_format($nextReview->notification_km, 0, ',', '.') . ' km.';
        }

        return redirect()->back()
            ->with('success', $message);
    }
}
