<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\CatalogDeletionException;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Brand $brand): void {
            // Soft delete does not trip RESTRICT foreign keys.
            $count = $brand->devices()->withTrashed()->count();

            if ($count > 0) {
                throw CatalogDeletionException::referenced('brand', $count);
            }
        });
    }

    /**
     * @return HasMany<Device, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }
}
