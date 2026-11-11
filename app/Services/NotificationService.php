<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\DeviceDelivered;
use App\Events\PaymentConfirmed;
use App\Events\ServiceRequestAssigned;
use App\Events\ServiceRequestCreated;
use App\Events\ServiceStatusChanged;
use App\Jobs\SendEmailJob;
use App\Jobs\SendServiceNotificationJob;
use App\Jobs\SendSmsJob;
use App\Models\Notification;
use App\Notifications\DeviceReadyNotification;
use App\Notifications\FeedbackRequestNotification;
use App\Notifications\PaymentConfirmedNotification;
use App\Notifications\ServiceRequestCreatedNotification;
use App\Notifications\ServiceStatusChangedNotification;
use App\Notifications\TechnicianAssignedNotification;
use InvalidArgumentException;

final class NotificationService
{
    public function dispatchForEvent(object $event): void
    {
        $entityId = $this->entityId($event);

        foreach ($this->namesFor($event) as $name) {
            $this->dispatch($name, $entityId);
        }
    }

    public function dispatch(string $name, int $entityId): void
    {
        Notification::query()->create(['name' => $name]);

        SendServiceNotificationJob::dispatch($name, $entityId);
        SendSmsJob::dispatch($name, $entityId);
        SendEmailJob::dispatch($name, $entityId);
    }

    /**
     * @return list<string>
     */
    private function namesFor(object $event): array
    {
        return match (true) {
            $event instanceof ServiceRequestCreated => [ServiceRequestCreatedNotification::NAME],
            $event instanceof ServiceRequestAssigned => [TechnicianAssignedNotification::NAME],
            $event instanceof ServiceStatusChanged => [ServiceStatusChangedNotification::NAME],
            $event instanceof PaymentConfirmed => [
                PaymentConfirmedNotification::NAME,
                FeedbackRequestNotification::NAME,
            ],
            $event instanceof DeviceDelivered => [DeviceReadyNotification::NAME],
            default => throw new InvalidArgumentException('Event has no named notification.'),
        };
    }

    private function entityId(object $event): int
    {
        return match (true) {
            $event instanceof ServiceRequestCreated,
            $event instanceof ServiceRequestAssigned,
            $event instanceof ServiceStatusChanged => $event->serviceRequestId,
            $event instanceof PaymentConfirmed => $event->paymentId,
            $event instanceof DeviceDelivered => $event->deviceReceptionId,
            default => throw new InvalidArgumentException('Event has no entity id.'),
        };
    }
}
