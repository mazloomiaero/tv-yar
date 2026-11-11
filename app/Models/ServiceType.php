<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class ServiceType extends Model
{
    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsToMany<DeviceType, $this>
     */
    public function deviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(DeviceType::class, 'service_type_device_types');
    }

    /**
     * @return BelongsToMany<ServiceStatus, $this>
     */
    public function statuses(): BelongsToMany
    {
        return $this->belongsToMany(ServiceStatus::class, 'service_type_statuses');
    }

    /**
     * @return BelongsToMany<CustomField, $this>
     */
    public function customFields(): BelongsToMany
    {
        return $this->belongsToMany(CustomField::class, 'service_type_custom_fields');
    }
}
