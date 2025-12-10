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
        'last_notified_at',
        'recurring',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'notified' => 'boolean',
            'resolved' => 'boolean',
            'last_notified_at' => 'datetime',
            'recurring' => 'boolean',
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

    /**
     * Criar próxima ocorrência recorrente para o próximo ano
     * Mantém as mesmas informações mas atualiza a data de vencimento
     */
    public function createNextRecurrence(): ?self
    {
        // Não criar recorrência se:
        // 1. Já foi resolvida
        // 2. Não está marcada como recorrente
        // 3. Não é um tipo que pode ser recorrente (IPVA e Licenciamento)
        if ($this->resolved || !$this->recurring || !in_array($this->type, ['licenciamento', 'ipva'])) {
            return null;
        }

        // Calcular próxima data de vencimento (mesmo dia e mês do próximo ano)
        $nextDueDate = $this->due_date->copy()->addYear();

        // Verificar se já existe uma obrigação futura para este veículo e tipo
        $existingEvent = self::where('vehicle_id', $this->vehicle_id)
            ->where('type', $this->type)
            ->where('resolved', false)
            ->whereDate('due_date', '>=', $nextDueDate->copy()->subDays(30))
            ->whereDate('due_date', '<=', $nextDueDate->copy()->addDays(30))
            ->first();

        // Se já existe uma obrigação próxima, não criar duplicata
        if ($existingEvent) {
            return null;
        }

        // Criar nova obrigação para o próximo ano
        // Mantém o campo recurring para que continue sendo recorrente
        $nextEvent = self::create([
            'vehicle_id' => $this->vehicle_id,
            'type' => $this->type,
            'due_date' => $nextDueDate,
            'description' => $this->description,
            'recurring' => $this->recurring, // Mantém a recorrência
            'notified' => false,
            'resolved' => false,
        ]);

        return $nextEvent;
    }
}
