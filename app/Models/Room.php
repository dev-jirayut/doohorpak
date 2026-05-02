<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    protected $fillable = [
        'property_id', 'building_id', 'floor_id', 'room_number', 'floor',
        'room_type_id', 'status', 'description',
        'rent_price', 'electricity_type', 'electricity_rate',
        'water_type', 'water_rate',
        'has_internet', 'internet_fee', 'has_parking', 'parking_fee',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'has_internet' => 'boolean',
            'has_parking'  => 'boolean',
            'images'       => 'array',
            'rent_price'   => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floorModel(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRental(): HasOne
    {
        return $this->hasOne(Rental::class)->where('status', 'active');
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function charges(): BelongsToMany
    {
        return $this->belongsToMany(Charge::class, 'charge_rooms')
            ->withPivot('active_from', 'active_to')
            ->withTimestamps();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'ว่าง',
            'occupied'    => 'มีผู้เช่า',
            'maintenance' => 'ซ่อมบำรุง',
            default       => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available'   => 'success',
            'occupied'    => 'danger',
            'maintenance' => 'secondary',
            default       => 'secondary',
        };
    }

    public function getEffectiveRentAttribute(): float
    {
        return (float) ($this->rent_price ?? $this->roomType?->base_price ?? 0);
    }
}
