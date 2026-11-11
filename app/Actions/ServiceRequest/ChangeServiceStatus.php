<?php

declare(strict_types=1);

namespace App\Actions\ServiceRequest;

use App\Events\ServiceStatusChanged;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ChangeServiceStatus
{
    public function execute(ServiceRequest $serviceRequest, ServiceStatus $status, User $changedBy): ServiceRequest
    {
        $mapped = $serviceRequest->serviceType
            ->statuses()
            ->where('service_statuses.id', $status->id)
            ->exists();

        if (! $mapped) {
            throw new InvalidArgumentException(
                'Status is not allowed for this service type.',
            );
        }

        $serviceRequest = DB::transaction(function () use ($serviceRequest, $status, $changedBy): ServiceRequest {
            $serviceRequest->status_id = $status->id;
            $serviceRequest->save();

            $serviceRequest->statusHistory()->create([
                'service_status_id' => $status->id,
                'changed_by' => $changedBy->id,
            ]);

            return $serviceRequest;
        });

        ServiceStatusChanged::dispatch((int) $serviceRequest->id);

        return $serviceRequest;
    }
}
