<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fueling extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'date_time',
        'odometer',
        'fuel_type',
        'liters',
        'price_per_liter',
        'total_amount',
        'gas_station_name',
        'payment_method',
        'payment_method_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
            'odometer' => 'integer',
            'liters' => 'decimal:2',
            'price_per_liter' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Verifica se o abastecimento é completo (≥ 90% da capacidade do tanque)
     */
    public function getIsFullAttribute(): bool
    {
        if (!$this->vehicle || !$this->vehicle->tank_capacity) {
            return false;
        }

        $threshold = $this->vehicle->tank_capacity * 0.90;
        return $this->liters >= $threshold;
    }

    /**
     * Calcula o consumo real baseado em ciclos completos de abastecimento
     * 
     * @param int $vehicleId ID do veículo
     * @param string $startDate Data inicial (Y-m-d)
     * @param string $endDate Data final (Y-m-d)
     * @return array ['real_consumption' => float|null, 'period_consumption' => float|null, 'total_km' => int, 'total_liters' => float, 'full_count' => int]
     */
    public static function calculateRealConsumption(int $vehicleId, string $startDate, string $endDate): array
    {
        // Buscar abastecimentos do período ordenados por data
        $fuelings = self::where('vehicle_id', $vehicleId)
            ->whereBetween('date_time', [$startDate, $endDate])
            ->orderBy('date_time')
            ->orderBy('odometer')
            ->with('vehicle')
            ->get();

        if ($fuelings->isEmpty()) {
            return [
                'real_consumption' => null,
                'period_consumption' => null,
                'total_km' => 0,
                'total_liters' => 0,
                'full_count' => 0,
            ];
        }

        // Buscar KM inicial e final do período através das viagens
        $trips = \App\Models\Trip::where('vehicle_id', $vehicleId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('odometer_start')
            ->get();

        $totalKm = $trips->sum('km_total');
        $totalLiters = $fuelings->sum('liters');

        // Identificar abastecimentos completos
        $fullFuelings = $fuelings->filter(fn($f) => $f->is_full)->values();

        $fullCount = $fullFuelings->count();

        // Se houver 2 ou mais abastecimentos completos, calcular por ciclos
        if ($fullCount >= 2) {
            $cycleConsumptions = [];
            
            for ($i = 0; $i < $fullCount - 1; $i++) {
                $startFull = $fullFuelings[$i];
                $endFull = $fullFuelings[$i + 1];

                // KM rodado entre os dois abastecimentos completos
                $km = $endFull->odometer - $startFull->odometer;
                
                if ($km <= 0) {
                    continue; // Ignorar se KM for inválido
                }

                // Soma dos litros entre os dois abastecimentos completos
                // Incluir: primeiro completo + todos os parciais entre eles
                // Excluir: segundo completo (marca o fim do ciclo, mas ainda não foi usado)
                $litersBetween = $fuelings
                    ->filter(function($f) use ($startFull, $endFull) {
                        // Incluir o primeiro completo
                        if ($f->id === $startFull->id) {
                            return true;
                        }
                        
                        // Excluir o segundo completo
                        if ($f->id === $endFull->id) {
                            return false;
                        }
                        
                        // Incluir abastecimentos entre os dois (por data e odômetro)
                        $fDateTime = $f->date_time;
                        $startDateTime = $startFull->date_time;
                        $endDateTime = $endFull->date_time;
                        
                        // Se está entre as datas
                        if ($fDateTime > $startDateTime && $fDateTime < $endDateTime) {
                            return true;
                        }
                        
                        // Se está na mesma data do início, mas com odômetro maior ou igual
                        if ($fDateTime->format('Y-m-d') === $startDateTime->format('Y-m-d') 
                            && $f->odometer >= $startFull->odometer
                            && $f->id !== $startFull->id) {
                            return true;
                        }
                        
                        return false;
                    })
                    ->sum('liters');

                if ($litersBetween > 0) {
                    $cycleConsumption = $km / $litersBetween;
                    $cycleConsumptions[] = $cycleConsumption;
                }
            }

            // Calcular média dos ciclos
            $realConsumption = !empty($cycleConsumptions) 
                ? round(array_sum($cycleConsumptions) / count($cycleConsumptions), 2)
                : null;
        } else {
            $realConsumption = null;
        }

        // Cálculo por período (fallback ou quando não há 2 completos)
        $periodConsumption = ($totalLiters > 0 && $totalKm > 0) 
            ? round($totalKm / $totalLiters, 2) 
            : null;

        return [
            'real_consumption' => $realConsumption,
            'period_consumption' => $periodConsumption,
            'total_km' => $totalKm,
            'total_liters' => $totalLiters,
            'full_count' => $fullCount,
        ];
    }
}
