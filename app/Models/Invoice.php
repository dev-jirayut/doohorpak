<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'property_id', 'invoice_number', 'rental_id',
        'month', 'year', 'due_date',
        'room_charge', 'electricity_units', 'electricity_rate', 'electricity_charge',
        'water_units', 'water_rate', 'water_charge',
        'other_charge', 'total_amount', 'status', 'paid_at', 'note',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'รอชำระ',
            'paid'      => 'ชำระแล้ว',
            'overdue'   => 'เกินกำหนด',
            'cancelled' => 'ยกเลิก',
            default     => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'warning',
            'paid'      => 'success',
            'overdue'   => 'danger',
            'cancelled' => 'secondary',
            default     => 'secondary',
        };
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
        return $months[$this->month] ?? '';
    }
}
