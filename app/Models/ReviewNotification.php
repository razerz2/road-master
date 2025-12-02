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
        'active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'current_km' => 'integer',
            'notification_km' => 'integer',
            'last_notified_km' => 'integer',
            'active' => 'boolean',
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

        // Verificar se o odômetro atual atingiu ou ultrapassou o KM de notificação
        if ($currentOdometer < $this->notification_km) {
            return false;
        }

        // Evitar notificações duplicadas - só notificar se ainda não foi notificado neste KM
        // ou se o KM atual é maior que o último notificado
        if ($this->last_notified_km !== null && $currentOdometer <= $this->last_notified_km) {
            return false;
        }

        return true;
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
}
