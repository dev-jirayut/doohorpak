<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $display_label  tenant name + room or LINE display_name
 */
class LineConversation extends Model
{
    protected $fillable = [
        'property_id', 'line_user_id', 'display_name', 'picture_url',
        'tenant_id', 'chat_name', 'last_message_at', 'has_unread',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'has_unread'      => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LineMessage::class, 'conversation_id')->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(LineMessage::class, 'conversation_id')->latest()->limit(1);
    }

    /** Resolved display name: custom override → tenant+room → LINE display name */
    public function getDisplayLabelAttribute(): string
    {
        if ($this->chat_name) {
            return $this->chat_name;
        }

        if ($this->tenant) {
            $room = $this->tenant->activeRental?->room?->room_number;
            return $room ? "{$this->tenant->name} - ห้อง {$room}" : $this->tenant->name;
        }

        return $this->display_name ?? $this->line_user_id;
    }
}
