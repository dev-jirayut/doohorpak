<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineWebhookLog extends Model
{
    protected $table = 'line_webhook_log';

    protected $fillable = [
        'property_id',
        'event_type',
        'webhook_event_id',
        'line_user_id',
        'status',
        'response_status',
        'message',
        'method',
        'url',
        'ip_address',
        'user_agent',
        'signature',
        'signature_valid',
        'headers',
        'payload',
        'response',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
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
