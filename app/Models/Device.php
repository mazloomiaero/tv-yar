<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'customer_id',
    'device_type_id',
    'brand_id',
    'model',
    'serial_number',
    'title',
    'purchase_date',
    'warranty_status',
    'warranty_until',
    'notes',
])]
class Device extends Model
{
    /** @use HasFactory<DeviceFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<DeviceType, $this>
     */
    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class);
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return HasMany<DeviceCustomFieldValue, $this>
     */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(DeviceCustomFieldValue::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_until' => 'date',
        ];
    }
}
