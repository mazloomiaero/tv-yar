<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TechnicianFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'branch_id',
    'user_id',
])]
class Technician extends Model
{
    /** @use HasFactory<TechnicianFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'technician_skills');
    }

    /**
     * @return HasMany<ServiceAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ServiceAssignment::class);
    }

    /**
     * @return HasMany<ServiceAssignment, $this>
     */
    public function openAssignments(): HasMany
    {
        return $this->assignments()->whereNull('completed_at');
    }

    public static function forUser(?User $user): ?self
    {
        if (! $user instanceof User) {
            return null;
        }

        $technician = self::query()->where('user_id', $user->id)->first();

        return $technician instanceof self ? $technician : null;
    }

    public function givenName(): string
    {
        if (filled($this->user?->first_name)) {
            return (string) $this->user->first_name;
        }

        $parts = preg_split('/\s+/u', trim((string) $this->name), 2) ?: [];

        return $parts[0] ?? '';
    }

    public function familyName(): string
    {
        if (filled($this->user?->last_name)) {
            return (string) $this->user->last_name;
        }

        $parts = preg_split('/\s+/u', trim((string) $this->name), 2) ?: [];

        return $parts[1] ?? '';
    }
}
