<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OmiseTransaction extends Model
{
    protected $fillable = [
        'invoice_id', 'property_id', 'omise_charge_id', 'omise_source_id',
        'payment_method', 'amount', 'currency', 'status',
        'failure_code', 'failure_message', 'authorize_uri',
        'metadata', 'charged_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'   => 'array',
            'charged_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function platformRevenue(): HasOne
    {
        return $this->hasOne(PlatformRevenue::class);
    }

    public function getAmountBahtAttribute(): float
    {
        return $this->amount / 100;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'credit_card' => 'บัตรเครดิต',
            'promptpay'   => 'พร้อมเพย์',
            default       => $this->payment_method,
        };
    }
}
