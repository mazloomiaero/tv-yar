<?php

declare(strict_types=1);

namespace App\Actions\Workshop;

use App\Actions\ServiceRequest\ChangeServiceStatus;
use App\Events\DeviceDelivered;
use App\Jobs\GenerateDocumentJob;
use App\Models\DeviceReception;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\User;
use App\Support\WorkshopServiceStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class DeliverDevice
{
    public function __construct(private readonly ChangeServiceStatus $changeServiceStatus) {}

    /**
     * @param  array{delivery_date: string}  $attributes
     */
    public function execute(ServiceRequest $serviceRequest, User $deliveredBy, array $attributes): DeviceReception
    {
        $reception = $serviceRequest->reception;

        if (! $reception instanceof DeviceReception) {
            throw new InvalidArgumentException(
                'This service request has no reception receipt.',
            );
        }

        if ($reception->isDelivered()) {
            throw new InvalidArgumentException(
                'This device has already been delivered.',
            );
        }

        $status = $this->statusNamed(WorkshopServiceStatus::Delivered);

        $reception = DB::transaction(function () use ($serviceRequest, $deliveredBy, $attributes, $reception, $status): DeviceReception {
            $reception->delivery_date = $attributes['delivery_date'];
            $reception->save();

            $this->changeServiceStatus->execute($serviceRequest, $status, $deliveredBy);

            return $reception;
        });

        DeviceDelivered::dispatch((int) $reception->id);
        GenerateDocumentJob::dispatch((int) $reception->id);

        return $reception;
    }

    private function statusNamed(string $name): ServiceStatus
    {
        $status = ServiceStatus::query()->where('name', $name)->first();

        if (! $status instanceof ServiceStatus) {
            throw new RuntimeException('Seeded status ['.$name.'] is missing.');
        }

        return $status;
    }
}
