<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'file_name',
        'year',
        'vehicle_id',
        'rows_imported',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'rows_imported' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

