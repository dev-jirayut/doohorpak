<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'request_number', 'property_id', 'room_id', 'tenant_id', 'assigned_to',
        'title', 'description', 'category', 'priority', 'status',
        'technician_note', 'resolved_at', 'image_path', 'video_path',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'รอดำเนินการ',
            'in_progress' => 'กำลังดำเนินการ',
            'done'        => 'เสร็จสิ้น',
            'cancelled'   => 'ยกเลิก',
            default       => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'     => 'warning',
            'in_progress' => 'info',
            'done'        => 'success',
            'cancelled'   => 'secondary',
            default       => 'secondary',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low'    => 'ต่ำ',
            'normal' => 'ปกติ',
            'high'   => 'สูง',
            'urgent' => 'เร่งด่วน',
            default  => $this->priority,
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'electrical' => 'ไฟฟ้า',
            'plumbing'   => 'ประปา',
            'furniture'  => 'เฟอร์นิเจอร์',
            'general'    => 'ทั่วไป',
            'other'      => 'อื่นๆ',
            default      => $this->category,
        };
    }
}
