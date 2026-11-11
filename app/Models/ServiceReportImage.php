<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_report_id',
    'path',
    'kind',
])]
class ServiceReportImage extends Model
{
    /**
     * @return BelongsTo<ServiceReport, $this>
     */
    public function serviceReport(): BelongsTo
    {
        return $this->belongsTo(ServiceReport::class);
    }
}
