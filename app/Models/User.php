<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'last_name', 'mobile', 'username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * @return HasOne<Technician, $this>
     */
    public function technician(): HasOne
    {
        return $this->hasOne(Technician::class);
    }

    public function hasRole(RoleName|string $role): bool
    {
        $name = $role instanceof RoleName ? $role->value : $role;

        return $this->roles()->where('roles.name', $name)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission): void {
                $query->where('permissions.name', $permission);
            })
            ->exists();
    }

    public function givenName(): string
    {
        if (filled($this->first_name)) {
            return (string) $this->first_name;
        }

        $parts = preg_split('/\s+/u', trim((string) $this->name), 2) ?: [];

        return $parts[0] ?? '';
    }

    public function familyName(): string
    {
        if (filled($this->last_name)) {
            return (string) $this->last_name;
        }

        $parts = preg_split('/\s+/u', trim((string) $this->name), 2) ?: [];

        return $parts[1] ?? '';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
