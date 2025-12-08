<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vehicle extends Model
{
    protected $fillable = [
        'name',
        'plate',
        'brand',
        'model',
        'year',
        'fuel_type',
        'tank_capacity',
        'km_inicial',
        'current_odometer',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'tank_capacity' => 'decimal:2',
            'km_inicial' => 'integer',
            'current_odometer' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function fuelings(): HasMany
    {
        return $this->hasMany(Fueling::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_vehicle');
    }

    public function reviewNotifications(): HasMany
    {
        return $this->hasMany(ReviewNotification::class);
    }

    public function mandatoryEvents(): HasMany
    {
        return $this->hasMany(VehicleMandatoryEvent::class);
    }

    public function fuelTypes(): BelongsToMany
    {
        return $this->belongsToMany(FuelType::class, 'vehicle_fuel_type');
    }
}
