<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\CatalogDeletionException;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'province',
    'city',
    'address',
    'phone',
    'is_default',
    'status',
])]
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Branch $branch): void {
            // Soft delete does not trip RESTRICT foreign keys.
            $count = $branch->technicians()->withTrashed()->count()
                + $branch->assignments()->count();

            if ($count > 0) {
                throw CatalogDeletionException::referenced('branch', $count);
            }
        });
    }

    /**
     * @return HasMany<Technician, $this>
     */
    public function technicians(): HasMany
    {
        return $this->hasMany(Technician::class);
    }

    /**
     * @return HasMany<ServiceAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ServiceAssignment::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}
