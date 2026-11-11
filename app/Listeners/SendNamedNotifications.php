<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DeviceDelivered;
use App\Events\PaymentConfirmed;
use App\Events\ServiceRequestAssigned;
use App\Events\ServiceRequestCreated;
use App\Events\ServiceStatusChanged;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class SendNamedNotifications implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly NotificationService $notifications) {}

    public function handleServiceRequestCreated(ServiceRequestCreated $event): void
    {
        $this->notifications->dispatchForEvent($event);
    }

    public function handleServiceRequestAssigned(ServiceRequestAssigned $event): void
    {
        $this->notifications->dispatchForEvent($event);
    }

    public function handleServiceStatusChanged(ServiceStatusChanged $event): void
    {
        $this->notifications->dispatchForEvent($event);
    }

    public function handlePaymentConfirmed(PaymentConfirmed $event): void
    {
        $this->notifications->dispatchForEvent($event);
    }

    public function handleDeviceDelivered(DeviceDelivered $event): void
    {
        $this->notifications->dispatchForEvent($event);
    }
}
