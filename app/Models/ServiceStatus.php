<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\CatalogDeletionException;
use Database\Factories\ServiceStatusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'code', 'is_system'])]
class ServiceStatus extends Model
{
    /** @use HasFactory<ServiceStatusFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (ServiceStatus $status): void {
            // Soft delete does not trip RESTRICT foreign keys.
            $count = $status->referencedRecordCount();

            if ($count > 0) {
                throw CatalogDeletionException::referenced('service status', $count);
            }

            if ($status->is_system) {
                throw CatalogDeletionException::systemStatus();
            }
        });
    }

    public static function findByCode(string $code): ?self
    {
        $status = static::query()->where('code', $code)->first();

        return $status instanceof self ? $status : null;
    }

    /**
     * @return BelongsToMany<ServiceType, $this>
     */
    public function serviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(ServiceType::class, 'service_type_statuses');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    private function referencedRecordCount(): int
    {
        return ServiceRequest::query()->where('status_id', $this->id)->count()
            + ServiceStatusHistory::query()->where('service_status_id', $this->id)->count();
    }
}
