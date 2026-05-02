<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformRevenue extends Model
{
    protected $fillable = [
        'property_id', 'omise_transaction_id', 'invoice_id', 'payment_id',
        'type', 'payment_channel', 'gross_amount', 'fee_amount', 'net_amount',
        'billing_month', 'billing_year', 'status', 'transferred_at', 'transfer_ref', 'note',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount'   => 'decimal:2',
            'fee_amount'     => 'decimal:2',
            'net_amount'     => 'decimal:2',
            'billing_month'   => 'integer',
            'billing_year'    => 'integer',
            'transferred_at' => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function omiseTransaction(): BelongsTo
    {
        return $this->belongsTo(OmiseTransaction::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'percentage_fee' => 'หัก % ต่อรายการ',
            'package_fee'    => 'แพ็กเกจรายห้อง',
            'owner_payout'    => 'ยอดรอโอนให้เจ้าของ',
            default          => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'unpaid'      => 'รอเก็บเงิน',
            'paid'        => 'เก็บเงินแล้ว',
            'pending'     => 'รอโอน',
            'transferred' => 'โอนแล้ว',
            default       => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid', 'transferred' => 'badge-success',
            'unpaid'             => 'badge-danger',
            'pending'            => 'badge-warning',
            default              => 'badge-secondary',
        };
    }
}
