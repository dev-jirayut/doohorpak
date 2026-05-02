<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcel extends Model
{
    protected $fillable = [
        'parcel_number', 'property_id', 'room_id', 'tenant_id',
        'type', 'sender', 'carrier', 'tracking_number',
        'description', 'status', 'received_by',
        'received_at', 'notified_at', 'collected_at',
        'image_path', 'video_path', 'note',
    ];

    protected function casts(): array
    {
        return [
            'received_at'  => 'datetime',
            'notified_at'  => 'datetime',
            'collected_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'parcel'   => 'พัสดุ',
            'letter'   => 'จดหมาย',
            'document' => 'เอกสาร',
            'food'     => 'อาหาร',
            default    => 'พัสดุ',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'parcel'   => '📦',
            'letter'   => '✉️',
            'document' => '📄',
            'food'     => '🍱',
            default    => '📦',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'waiting'   => 'รอรับ',
            'notified'  => 'แจ้งแล้ว',
            'collected' => 'รับแล้ว',
            'returned'  => 'ส่งคืน',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'waiting'   => 'warning',
            'notified'  => 'info',
            'collected' => 'success',
            'returned'  => 'secondary',
            default     => 'secondary',
        };
    }
}
