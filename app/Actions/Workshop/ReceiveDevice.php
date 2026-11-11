<?php

declare(strict_types=1);

namespace App\Actions\Workshop;

use App\Actions\ServiceRequest\ChangeServiceStatus;
use App\Events\DeviceReceived;
use App\Jobs\GenerateDocumentJob;
use App\Models\DeviceReception;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\User;
use App\Services\DocumentNumberService;
use App\Support\DocumentType;
use App\Support\WorkshopServiceStatus;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class ReceiveDevice
{
    public function __construct(
        private readonly DocumentNumberService $documentNumbers,
        private readonly ChangeServiceStatus $changeServiceStatus,
    ) {}

    /**
     * @param  array{
     *     received_date: string,
     *     appearance: string,
     *     accompanying_items: string,
     *     reason: string,
     *     notes: string
     * }  $attributes
     */
    public function execute(ServiceRequest $serviceRequest, User $receivedBy, array $attributes, int $year): DeviceReception
    {
        if ($serviceRequest->reception()->exists()) {
            throw new InvalidArgumentException(
                'This service request already has a reception receipt.',
            );
        }

        $status = $this->statusNamed(WorkshopServiceStatus::DeviceReceived);

        $reception = DB::transaction(function () use ($serviceRequest, $receivedBy, $attributes, $year, $status): DeviceReception {
            $reception = DeviceReception::query()->create([
                'service_request_id' => $serviceRequest->id,
                'receipt_number' => $this->documentNumbers->nextNumber(DocumentType::Reception->value, $year),
                'customer_id' => $serviceRequest->customer_id,
                'device_id' => $serviceRequest->device_id,
                'received_by' => $receivedBy->id,
                'received_date' => $attributes['received_date'],
                'appearance' => $attributes['appearance'],
                'accompanying_items' => $attributes['accompanying_items'],
                'reason' => $attributes['reason'],
                'notes' => $attributes['notes'],
                'delivery_date' => null,
            ]);

            $this->changeServiceStatus->execute($serviceRequest, $status, $receivedBy);

            return $reception;
        });

        DeviceReceived::dispatch((int) $reception->id);
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
