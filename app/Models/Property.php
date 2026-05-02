<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'address', 'phone', 'type', 'is_active',
        'bank_account_name', 'bank_account_number', 'bank_name',
        'promptpay_id', 'qr_payment_image',
        'revenue_model', 'revenue_percentage', 'revenue_package_per_room',
    ];

    protected function casts(): array
    {
        return [
            'is_active'                => 'boolean',
            'revenue_percentage'       => 'decimal:2',
            'revenue_package_per_room' => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'property_users')->withTimestamps();
    }

    public function charges(): HasMany
    {
        return $this->hasMany(Charge::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function lineSetting(): HasOne
    {
        return $this->hasOne(LineSetting::class);
    }

    public function platformRevenues(): HasMany
    {
        return $this->hasMany(PlatformRevenue::class);
    }

    public function getTotalRoomsAttribute(): int
    {
        return $this->rooms()->count();
    }

    public function getOccupiedRoomsAttribute(): int
    {
        return $this->rooms()->where('status', 'occupied')->count();
    }

    public function getVacantRoomsAttribute(): int
    {
        return $this->rooms()->where('status', 'available')->count();
    }
}
