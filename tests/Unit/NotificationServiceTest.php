<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Events\InvoiceCreated;
use App\Events\PaymentConfirmed;
use App\Jobs\SendEmailJob;
use App\Jobs\SendServiceNotificationJob;
use App\Jobs\SendSmsJob;
use App\Notifications\FeedbackRequestNotification;
use App\Notifications\PaymentConfirmedNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use InvalidArgumentException;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_persists_name_only_and_queues_stub_jobs(): void
    {
        Bus::fake([SendSmsJob::class, SendEmailJob::class, SendServiceNotificationJob::class]);

        $service = app(NotificationService::class);
        $service->dispatch(PaymentConfirmedNotification::NAME, 9);

        $this->assertDatabaseHas('notifications', ['name' => PaymentConfirmedNotification::NAME]);
        $this->assertDatabaseCount('notifications', 1);

        Bus::assertDispatched(SendSmsJob::class);
        Bus::assertDispatched(SendEmailJob::class);
        Bus::assertDispatched(SendServiceNotificationJob::class);
    }

    public function test_payment_confirmed_maps_feedback_request_without_a_new_event(): void
    {
        Bus::fake([SendSmsJob::class, SendEmailJob::class, SendServiceNotificationJob::class]);

        app(NotificationService::class)->dispatchForEvent(new PaymentConfirmed(4));

        $this->assertDatabaseHas('notifications', ['name' => FeedbackRequestNotification::NAME]);
    }

    public function test_undocumented_events_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(NotificationService::class)->dispatchForEvent(new InvoiceCreated(1));
    }
}
