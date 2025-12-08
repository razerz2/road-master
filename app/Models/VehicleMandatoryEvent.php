<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMandatoryEvent extends Model
{
    protected $fillable = [
        'vehicle_id',
        'type',
        'due_date',
        'notified',
        'resolved',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'notified' => 'boolean',
            'resolved' => 'boolean',
        ];
    }

    /**
     * Tipos de obrigação disponíveis
     */
    public static function getTypes(): array
    {
        return [
            'licenciamento' => 'Licenciamento',
            'ipva' => 'IPVA',
            'multa' => 'Multa',
        ];
    }

    /**
     * Obter nome do tipo de obrigação
     */
    public function getTypeNameAttribute(): string
    {
        $types = self::getTypes();
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Verificar se deve disparar notificação
     */
    public function shouldNotify(): bool
    {
        if ($this->resolved) {
            return false;
        }

        if ($this->notified) {
            return false;
        }

        // Notificar 10 dias antes do vencimento ou se já venceu
        $daysUntilDue = now()->diffInDays($this->due_date, false);
        
        // Notificar se está dentro de 10 dias antes OU se já venceu
        return $daysUntilDue <= 10;
    }

    /**
     * Relacionamento com veículo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope para eventos não resolvidos
     */
    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope para eventos próximos do vencimento
     */
    public function scopeUpcoming($query, int $days = 15)
    {
        return $query->where('resolved', false)
            ->whereDate('due_date', '<=', now()->addDays($days))
            ->whereDate('due_date', '>=', now());
    }

    /**
     * Scope para eventos vencidos
     */
    public function scopeOverdue($query)
    {
        return $query->where('resolved', false)
            ->whereDate('due_date', '<', now());
    }
}
