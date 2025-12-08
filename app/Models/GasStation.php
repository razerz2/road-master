<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GasStation extends Model
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

    public function fuelings(): HasMany
    {
        return $this->hasMany(Fueling::class);
    }
}
