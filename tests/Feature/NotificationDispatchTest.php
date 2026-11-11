<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\DeviceDelivered;
use App\Events\InvoiceCreated;
use App\Events\PaymentConfirmed;
use App\Events\ServiceRequestAssigned;
use App\Events\ServiceRequestCreated;
use App\Events\ServiceStatusChanged;
use App\Jobs\SendEmailJob;
use App\Jobs\SendServiceNotificationJob;
use App\Jobs\SendSmsJob;
use App\Listeners\SendNamedNotifications;
use App\Notifications\DeviceReadyNotification;
use App\Notifications\FeedbackRequestNotification;
use App\Notifications\PaymentConfirmedNotification;
use App\Notifications\ServiceRequestCreatedNotification;
use App\Notifications\ServiceStatusChangedNotification;
use App\Notifications\TechnicianAssignedNotification;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_listeners_are_queued(): void
    {
        Queue::fake();

        event(new PaymentConfirmed(7));

        Queue::assertPushed(
            CallQueuedListener::class,
            fn (CallQueuedListener $job): bool => $job->class === SendNamedNotifications::class
                && $job->method === 'handlePaymentConfirmed',
        );
    }

    public function test_payment_confirmed_dispatches_payment_and_feedback_request_notifications(): void
    {
        Bus::fake([SendSmsJob::class, SendEmailJob::class, SendServiceNotificationJob::class]);

        event(new PaymentConfirmed(15));

        $this->assertDatabaseHas('notifications', ['name' => PaymentConfirmedNotification::NAME]);
        $this->assertDatabaseHas('notifications', ['name' => FeedbackRequestNotification::NAME]);

        Bus::assertDispatched(
            SendSmsJob::class,
            fn (SendSmsJob $job): bool => $job->notificationName === PaymentConfirmedNotification::NAME
                && $job->entityId === 15,
        );
        Bus::assertDispatched(
            SendSmsJob::class,
            fn (SendSmsJob $job): bool => $job->notificationName === FeedbackRequestNotification::NAME
                && $job->entityId === 15,
        );
        Bus::assertDispatchedTimes(SendEmailJob::class, 2);
        Bus::assertDispatchedTimes(SendServiceNotificationJob::class, 2);
    }

    public function test_device_delivered_dispatches_device_ready_notification(): void
    {
        Bus::fake([SendSmsJob::class, SendEmailJob::class, SendServiceNotificationJob::class]);

        event(new DeviceDelivered(3));

        $this->assertDatabaseHas('notifications', ['name' => DeviceReadyNotification::NAME]);
        Bus::assertDispatched(
            SendServiceNotificationJob::class,
            fn (SendServiceNotificationJob $job): bool => $job->notificationName === DeviceReadyNotification::NAME
                && $job->entityId === 3,
        );
    }

    public function test_service_request_events_dispatch_named_notifications(): void
    {
        Bus::fake([SendSmsJob::class, SendEmailJob::class, SendServiceNotificationJob::class]);

        event(new ServiceRequestCreated(1));
        event(new ServiceRequestAssigned(1));
        event(new ServiceStatusChanged(1));

        $this->assertDatabaseHas('notifications', ['name' => ServiceRequestCreatedNotification::NAME]);
        $this->assertDatabaseHas('notifications', ['name' => TechnicianAssignedNotification::NAME]);
        $this->assertDatabaseHas('notifications', ['name' => ServiceStatusChangedNotification::NAME]);
    }

    public function test_invoice_created_does_not_persist_a_named_notification(): void
    {
        event(new InvoiceCreated(1));

        $this->assertDatabaseCount('notifications', 0);
    }
}
