<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CustomFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class CustomField extends Model
{
    /** @use HasFactory<CustomFieldFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return HasMany<CustomFieldOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(CustomFieldOption::class);
    }

    /**
     * @return BelongsToMany<DeviceType, $this>
     */
    public function deviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(DeviceType::class, 'device_type_custom_fields');
    }

    /**
     * @return BelongsToMany<ServiceType, $this>
     */
    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'service_type_custom_fields');
    }

    /**
     * Select vs free text is inferred from whether this field has options.
     */
    public function isSelect(): bool
    {
        if ($this->relationLoaded('options')) {
            return $this->options->isNotEmpty();
        }

        return $this->options()->exists();
    }
}
