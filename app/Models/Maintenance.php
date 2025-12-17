<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    protected $fillable = [
        'vehicle_id',
        'user_id',
        'date',
        'odometer',
        'type',
        'maintenance_type_id',
        'description',
        'provider',
        'cost',
        'next_due_date',
        'next_due_odometer',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'odometer' => 'integer',
            'cost' => 'decimal:2',
            'next_due_date' => 'date',
            'next_due_odometer' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
