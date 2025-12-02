<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
}
