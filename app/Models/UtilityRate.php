<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtilityRate extends Model
{
    protected $fillable = [
        'electricity_rate', 'water_rate',
        'effective_from', 'effective_to', 'is_active',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'is_active'      => 'boolean',
    ];

    public static function current(): ?self
    {
        return self::where('is_active', true)
            ->orderBy('effective_from', 'desc')
            ->first();
    }
}
