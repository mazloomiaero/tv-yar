<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'request_number',
    'customer_id',
    'device_id',
    'service_type_id',
    'address_id',
    'status_id',
    'source',
    'priority',
    'problem_description',
    'preferred_date',
    'preferred_start_time',
    'preferred_end_time',
    'notes',
    'created_by',
    'closed_at',
])]
class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * @return BelongsTo<ServiceType, $this>
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * @return BelongsTo<CustomerAddress, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'address_id');
    }

    /**
     * @return BelongsTo<ServiceStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ServiceStatus::class, 'status_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<ServiceStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(ServiceStatusHistory::class);
    }

    /**
     * @return HasMany<ServiceRequestCustomFieldValue, $this>
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(ServiceRequestCustomFieldValue::class);
    }

    /**
     * @return HasMany<ServiceAssignment, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ServiceAssignment::class);
    }

    /**
     * @return HasMany<ServiceReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ServiceReport::class);
    }

    /**
     * @return HasOne<DeviceReception, $this>
     */
    public function reception(): HasOne
    {
        return $this->hasOne(DeviceReception::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return HasMany<Feedback, $this>
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function isAssignedToUser(User $user): bool
    {
        return $this->assignments()
            ->whereHas(
                'technician',
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->exists();
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }
}
