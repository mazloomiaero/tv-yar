<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DeviceReceptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_request_id',
    'receipt_number',
    'customer_id',
    'device_id',
    'received_by',
    'received_date',
    'appearance',
    'accompanying_items',
    'reason',
    'notes',
    'delivery_date',
])]
class DeviceReception extends Model
{
    /** @use HasFactory<DeviceReceptionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ServiceRequest, $this>
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

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
     * @return BelongsTo<User, $this>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isDelivered(): bool
    {
        return $this->delivery_date !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'delivery_date' => 'date',
        ];
    }
}
