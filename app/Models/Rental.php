<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $fillable = [
        'property_id', 'room_id', 'tenant_id', 'monthly_rent', 'deposit_amount',
        'occupants', 'electricity_deposit', 'start_date', 'end_date', 'status', 'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function contract(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Contract::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'     => 'อยู่อาศัย',
            'terminated' => 'ออกแล้ว',
            'pending'    => 'รอเข้าพัก',
            default      => $this->status,
        };
    }
}
