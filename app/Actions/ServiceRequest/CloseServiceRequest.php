<?php

declare(strict_types=1);

namespace App\Actions\ServiceRequest;

use App\Events\ServiceRequestClosed;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CloseServiceRequest
{
    public function execute(ServiceRequest $serviceRequest): ServiceRequest
    {
        if ($serviceRequest->isClosed()) {
            throw new InvalidArgumentException('Service request is already closed.');
        }

        $serviceRequest = DB::transaction(function () use ($serviceRequest): ServiceRequest {
            $serviceRequest->closed_at = now();
            $serviceRequest->save();

            return $serviceRequest;
        });

        ServiceRequestClosed::dispatch((int) $serviceRequest->id);

        return $serviceRequest;
    }
}
