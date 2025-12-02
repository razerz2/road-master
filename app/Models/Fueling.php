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
}
