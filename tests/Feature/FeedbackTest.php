<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\FeedbackSubmitted;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\ServiceRequest;
use App\Support\RoleName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRbac();
    }

    public function test_customer_submits_score_and_comment_for_own_service_request(): void
    {
        Event::fake([FeedbackSubmitted::class]);

        $customer = Customer::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.service-requests.feedback.store', $serviceRequest), [
                'score' => 'good',
                'comment' => 'On time.',
            ])
            ->assertRedirect(route('customer.service-requests.feedback.create', $serviceRequest));

        $feedback = Feedback::query()->first();
        $this->assertNotNull($feedback);
        $this->assertSame($serviceRequest->id, $feedback->service_request_id);
        $this->assertSame('good', $feedback->score);
        $this->assertSame('On time.', $feedback->comment);

        Event::assertDispatched(
            FeedbackSubmitted::class,
            fn (FeedbackSubmitted $event): bool => $event->feedbackId === $feedback->id,
        );
    }

    public function test_customer_cannot_submit_feedback_for_another_customers_request(): void
    {
        $owner = Customer::factory()->create();
        $other = Customer::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['customer_id' => $owner->id]);

        $this->actingAs($other, 'customer')
            ->post(route('customer.service-requests.feedback.store', $serviceRequest), [
                'score' => 'good',
                'comment' => 'Nope',
            ])
            ->assertNotFound();

        $this->assertSame(0, Feedback::query()->count());
    }

    public function test_guest_is_redirected_from_customer_feedback(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $this->get(route('customer.service-requests.feedback.create', $serviceRequest))
            ->assertRedirect(route('customer.login'));
    }

    public function test_score_is_required_without_a_one_to_five_scale(): void
    {
        $customer = Customer::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer, 'customer')
            ->from(route('customer.service-requests.feedback.create', $serviceRequest))
            ->post(route('customer.service-requests.feedback.store', $serviceRequest), [
                'comment' => 'Missing score',
            ])
            ->assertRedirect(route('customer.service-requests.feedback.create', $serviceRequest))
            ->assertSessionHasErrors('score');
    }

    public function test_operator_can_view_feedback_and_technician_cannot(): void
    {
        $feedback = Feedback::factory()->create(['score' => 'good', 'comment' => 'Fine']);

        $operator = $this->staffUser(RoleName::Operator);
        $this->actingAs($operator)
            ->get(route('operator.feedback.index'))
            ->assertOk()
            ->assertSee('good');

        $this->actingAs($operator)
            ->get(route('operator.feedback.show', $feedback))
            ->assertOk()
            ->assertSee('Fine');

        $technician = $this->staffUser(RoleName::Technician);
        $this->assertFalse($technician->can('viewAny', Feedback::class));
        $this->actingAs($technician)
            ->get(route('operator.feedback.index'))
            ->assertForbidden();
    }
}
