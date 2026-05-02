<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineMessage extends Model
{
    protected $fillable = [
        'conversation_id', 'line_message_id', 'direction',
        'type', 'content', 'metadata', 'sent_by_user_id',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(LineConversation::class, 'conversation_id');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function isInbound(): bool  { return $this->direction === 'inbound'; }
    public function isOutbound(): bool { return $this->direction === 'outbound'; }
}
