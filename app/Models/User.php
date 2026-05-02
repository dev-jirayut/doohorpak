<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $role_label
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string|null $phone
 * @property string|null $line_user_id
 * @property string|null $avatar
 */

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'line_user_id', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isOwner(): bool      { return $this->role === 'owner'; }
    public function isStaff(): bool      { return $this->role === 'staff'; }
    public function isTenantUser(): bool { return $this->role === 'tenant'; }
    public function isAdmin(): bool      { return in_array($this->role, ['super_admin', 'owner', 'staff']); }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'owner'       => 'เจ้าของหอ',
            'staff'       => 'พนักงาน',
            'tenant'      => 'ผู้เช่า',
            default       => $this->role ?? 'ไม่ระบุ',
        };
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_users')->withTimestamps();
    }

    public function ownedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function canAccessProperty(int $propertyId): bool
    {
        if ($this->isSuperAdmin()) return true;
        if ($this->isOwner()) return $this->ownedProperties()->where('id', $propertyId)->exists();
        return $this->properties()->where('properties.id', $propertyId)->exists();
    }
}
