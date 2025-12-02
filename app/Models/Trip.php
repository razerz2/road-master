<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'date',
        'origin_location_id',
        'destination_location_id',
        'return_to_origin',
        'departure_time',
        'return_time',
        'odometer_start',
        'odometer_end',
        'km_total',
        'purpose',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'return_to_origin' => 'boolean',
            'odometer_start' => 'integer',
            'odometer_end' => 'integer',
            'km_total' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(TripStop::class)->orderBy('sequence');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($trip) {
            // Garantir que created_by seja sempre definido ANTES de criar
            // Isso é crítico para importações em background onde auth() não está disponível
            if (!$trip->created_by || $trip->created_by === null) {
                // Tentar obter do cache - verificar todas as chaves de importação ativas
                // Isso é um fallback para quando o Laravel Excel não preserva o atributo
                $cacheKeys = \Illuminate\Support\Facades\Cache::get('active_imports', []);
                foreach ($cacheKeys as $importId) {
                    $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$importId}", []);
                    if (isset($progress['user_id']) && $progress['status'] === 'processing') {
                        $trip->created_by = (int) $progress['user_id'];
                        break;
                    }
                }
                
                // Se ainda não tiver, tentar auth()->id() como último recurso
                if (!$trip->created_by && auth()->id()) {
                    $trip->created_by = auth()->id();
                }
            }
        });

        static::saving(function ($trip) {
            if ($trip->odometer_end && $trip->odometer_start) {
                $trip->km_total = $trip->odometer_end - $trip->odometer_start;
            }
        });
    }

    /**
     * Sobrescrever performInsert para garantir que created_by seja sempre incluído
     * mesmo quando o Laravel Excel usa insertGetId diretamente
     */
    protected function performInsert(\Illuminate\Database\Eloquent\Builder $query)
    {
        // Garantir que created_by esteja definido antes de inserir
        if (!isset($this->attributes['created_by']) || $this->attributes['created_by'] === null) {
            // Tentar obter do cache
            $cacheKeys = \Illuminate\Support\Facades\Cache::get('active_imports', []);
            $userId = null;
            
            foreach ($cacheKeys as $importId) {
                $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$importId}", []);
                if (isset($progress['user_id']) && $progress['status'] === 'processing') {
                    $userId = (int) $progress['user_id'];
                    break;
                }
            }
            
            // Se ainda não tiver, tentar auth()->id()
            if (!$userId && auth()->id()) {
                $userId = auth()->id();
            }
            
            // Se ainda não tiver, lançar exceção
            if (!$userId) {
                \Log::error('Trip::performInsert - created_by não encontrado', [
                    'attributes' => $this->attributes,
                    'cache_keys' => $cacheKeys,
                ]);
                throw new \Exception('Não foi possível determinar o usuário criador. created_by é obrigatório.');
            }
            
            // DEFINIR DIRETAMENTE NOS ATRIBUTOS
            $this->attributes['created_by'] = $userId;
            $this->created_by = $userId;
        }
        
        // Garantir que created_by esteja nos atributos que serão inseridos
        // Forçar o valor mesmo que já esteja definido
        if (isset($this->attributes['created_by'])) {
            $this->attributes['created_by'] = (int) $this->attributes['created_by'];
        }
        
        return parent::performInsert($query);
    }
}
