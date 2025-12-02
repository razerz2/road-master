<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    protected $fillable = [
        'name',
        'type',
        'location_type_id',
        'address',
        'street',
        'number',
        'complement',
        'neighborhood',
        'zip_code',
        'city',
        'state',
        'notes',
    ];

    public function originTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'origin_location_id');
    }

    public function destinationTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'destination_location_id');
    }

    public function locationType(): BelongsTo
    {
        return $this->belongsTo(LocationType::class);
    }
}
