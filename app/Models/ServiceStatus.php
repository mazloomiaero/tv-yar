<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class ServiceStatus extends Model
{
    /** @use HasFactory<ServiceStatusFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsToMany<ServiceType, $this>
     */
    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'service_type_statuses');
    }
}
