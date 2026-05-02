<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Charge extends Model
{
    protected $fillable = ['property_id', 'name', 'amount', 'type', 'description', 'is_active'];

    protected function casts(): array
    {
        return [
            'amount'    => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'charge_rooms')
            ->withPivot('active_from', 'active_to')
            ->withTimestamps();
    }
}
