<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'service_request_id',
])]
class ServiceReport extends Model
{
    /** @use HasFactory<ServiceReportFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ServiceRequest, $this>
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * @return HasMany<ServiceReportPart, $this>
     */
    public function parts(): HasMany
    {
        return $this->hasMany(ServiceReportPart::class);
    }

    /**
     * @return HasMany<ServiceReportImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ServiceReportImage::class);
    }
}
