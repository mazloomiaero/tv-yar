<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_request_id',
    'branch_id',
    'technician_id',
    'is_suggested',
    'assigned_by',
    'scheduled_date',
    'start_time',
    'end_time',
    'status',
    'technician_response',
    'accepted_at',
    'started_at',
    'completed_at',
])]
class ServiceAssignment extends Model
{
    /** @use HasFactory<ServiceAssignmentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ServiceRequest, $this>
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<Technician, $this>
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeScheduledOn(Builder $query, ?string $date): Builder
    {
        return $query->when(
            $date !== null && $date !== '',
            fn (Builder $scheduled) => $scheduled->whereDate('scheduled_date', $date),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_suggested' => 'boolean',
            'scheduled_date' => 'date',
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
