<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $fillable = [
        'provider',
        'property_id',
        'event_type',
        'external_id',
        'status',
        'response_status',
        'message',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'headers',
        'payload',
        'response',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'payload' => 'array',
            'response' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
