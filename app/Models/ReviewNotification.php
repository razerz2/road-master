<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewNotification extends Model
{
    protected $fillable = [
        'vehicle_id',
        'review_type',
        'name',
        'current_km',
        'notification_km',
        'last_notified_km',
        'last_notified_at',
        'completed_at',
        'completed_km',
        'active',
        'description',
        'recurring',
        'recurrence_interval_km',
    ];

    protected function casts(): array
    {
        return [
            'current_km' => 'integer',
            'notification_km' => 'integer',
            'last_notified_km' => 'integer',
            'completed_km' => 'integer',
            'recurrence_interval_km' => 'integer',
            'last_notified_at' => 'datetime',
            'completed_at' => 'datetime',
            'active' => 'boolean',
            'recurring' => 'boolean',
        ];
    }

    /**
     * Tipos de revisão disponíveis
     */
    public static function getReviewTypes(): array
    {
        return [
            'troca_oleo' => 'Troca de Óleo',
            'revisao_manutencao' => 'Revisão para Manutenção',
            'lavagem' => 'Lavagem',
            'pneu' => 'Troca/Revisão de Pneus',
            'freio' => 'Revisão de Freios',
            'suspensao' => 'Revisão de Suspensão',
            'filtro_ar' => 'Troca de Filtro de Ar',
            'filtro_combustivel' => 'Troca de Filtro de Combustível',
            'bateria' => 'Troca de Bateria',
            'alinhamento' => 'Alinhamento e Balanceamento',
            'outro' => 'Outro',
        ];
    }

    /**
     * Obter nome do tipo de revisão
     */
    public function getReviewTypeNameAttribute(): string
    {
        $types = self::getReviewTypes();
        return $types[$this->review_type] ?? $this->review_type;
    }

    /**
     * Verificar se deve disparar notificação
     */
    public function shouldNotify(int $currentOdometer): bool
    {
        if (!$this->active) {
            return false;
        }

        // Não notificar se já foi realizada
        if ($this->completed_at !== null) {
            return false;
        }

        // Verificar se o odômetro atual atingiu ou ultrapassou o KM de notificação
        if ($currentOdometer < $this->notification_km) {
            return false;
        }

        return true;
    }

    /**
     * Marcar revisão como realizada
     */
    public function markAsCompleted(int $completedKm = null): void
    {
        $this->update([
            'completed_at' => now(),
            'completed_km' => $completedKm ?? $this->vehicle->current_odometer ?? null,
            'active' => false, // Desativar automaticamente quando marcada como realizada
        ]);
    }

    /**
     * Verificar se a revisão foi realizada
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Marcar como notificado
     */
    public function markAsNotified(int $currentOdometer): void
    {
        $this->update([
            'last_notified_km' => $currentOdometer,
        ]);
    }

    /**
     * Relacionamento com veículo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope para notificações ativas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para notificações de um veículo
     */
    public function scopeForVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /**
     * Criar próxima ocorrência recorrente baseada no intervalo de KM
     * Mantém as mesmas informações mas atualiza o KM de notificação
     * 
     * @param int|null $completedKm KM onde a revisão foi completada (opcional, usa completed_km ou current_odometer se não informado)
     */
    public function createNextRecurrence(?int $completedKm = null): ?self
    {
        // Não criar recorrência se:
        // 1. Não está marcada como recorrente
        // 2. Não tem intervalo de recorrência definido
        if (!$this->recurring || !$this->recurrence_interval_km || $this->recurrence_interval_km <= 0) {
            return null;
        }

        // Calcular próximo KM de notificação baseado no KM completado + intervalo
        $completedKm = $completedKm ?? $this->completed_km ?? $this->vehicle->current_odometer ?? 0;
        
        // Validar que o KM completado é válido (maior que zero)
        if ($completedKm <= 0) {
            return null;
        }
        
        $nextNotificationKm = $completedKm + $this->recurrence_interval_km;

        // Verificar se já existe uma revisão futura para este veículo e tipo
        // com KM de notificação próximo (±10% do intervalo)
        // IMPORTANTE: Excluir a própria revisão atual da verificação
        $tolerance = (int) ($this->recurrence_interval_km * 0.1);
        $existingReview = self::where('vehicle_id', $this->vehicle_id)
            ->where('review_type', $this->review_type)
            ->where('id', '!=', $this->id) // Excluir a própria revisão atual
            ->where('completed_at', null) // Não completada
            ->whereBetween('notification_km', [
                $nextNotificationKm - $tolerance,
                $nextNotificationKm + $tolerance
            ])
            ->first();

        // Se já existe uma revisão próxima, não criar duplicata
        if ($existingReview) {
            return null;
        }

        // Criar nova revisão para o próximo intervalo
        // Mantém o campo recurring para que continue sendo recorrente
        $nextReview = self::create([
            'vehicle_id' => $this->vehicle_id,
            'review_type' => $this->review_type,
            'name' => $this->name,
            'current_km' => $completedKm,
            'notification_km' => $nextNotificationKm,
            'description' => $this->description,
            'recurring' => $this->recurring, // Mantém a recorrência
            'recurrence_interval_km' => $this->recurrence_interval_km, // Mantém o intervalo
            'active' => true,
        ]);

        return $nextReview;
    }
}
