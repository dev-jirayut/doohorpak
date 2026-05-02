<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        'room_id', 'month', 'year',
        'electricity_previous', 'electricity_current',
        'water_previous', 'water_current', 'note',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getElectricityUnitsAttribute(): float
    {
        return max(0, $this->electricity_current - $this->electricity_previous);
    }

    public function getWaterUnitsAttribute(): float
    {
        return max(0, $this->water_current - $this->water_previous);
    }
}
