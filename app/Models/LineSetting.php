<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineSetting extends Model
{
    protected $fillable = [
        'property_id', 'notify_token',
        'oa_channel_id', 'oa_channel_secret', 'oa_channel_access_token',
        'admin_line_user_ids', 'webhook_url',
        'rich_menu_id', 'rich_menu_image_path', 'rich_menu_actions', 'rich_menu_created_at',
        'notify_on_invoice', 'notify_on_overdue',
        'notify_on_maintenance', 'notify_on_new_tenant',
        'reminder_time',
    ];

    protected function casts(): array
    {
        return [
            'notify_token'             => 'encrypted',
            'oa_channel_secret'        => 'encrypted',
            'oa_channel_access_token'  => 'encrypted',
            'notify_on_invoice'     => 'boolean',
            'notify_on_overdue'     => 'boolean',
            'notify_on_maintenance' => 'boolean',
            'notify_on_new_tenant'  => 'boolean',
            'admin_line_user_ids'   => 'array',
            'rich_menu_actions'     => 'array',
            'rich_menu_created_at'  => 'datetime',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
