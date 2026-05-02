<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    protected $fillable = [
        'rental_id', 'property_id', 'contract_number',
        'start_date', 'end_date', 'status',
        'file_path', 'tenant_id_card_copy_path',
        'paper_contract_image_path', 'paper_contract_image_paths',
        'tenant_signature', 'owner_signature',
        'tenant_signed_at', 'owner_signed_at',
        'reminder_30_sent', 'reminder_7_sent', 'terms',
    ];

    protected function casts(): array
    {
        return [
            'start_date'       => 'date',
            'end_date'         => 'date',
            'tenant_signed_at' => 'datetime',
            'owner_signed_at'  => 'datetime',
            'reminder_30_sent' => 'boolean',
            'reminder_7_sent'  => 'boolean',
            'paper_contract_image_paths' => 'array',
        ];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'ร่าง',
            'active'     => 'มีผล',
            'expired'    => 'หมดอายุ',
            'terminated' => 'ยกเลิก',
            default      => $this->status,
        };
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->end_date, false);
    }

    public function isSoonToExpire(): bool
    {
        return $this->days_until_expiry <= 30 && $this->days_until_expiry > 0;
    }

    public function getPaperContractImagesAttribute(): array
    {
        $paths = $this->paper_contract_image_paths ?? [];

        if ($this->paper_contract_image_path && !in_array($this->paper_contract_image_path, $paths, true)) {
            array_unshift($paths, $this->paper_contract_image_path);
        }

        return array_values(array_filter($paths));
    }
}
