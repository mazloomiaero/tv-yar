<?php

declare(strict_types=1);

namespace App\Actions\Feedback;

use App\Events\FeedbackSubmitted;
use App\Models\Customer;
use App\Models\Feedback;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateFeedback
{
    /**
     * @param  array{score: string, comment?: string|null}  $attributes
     */
    public function execute(Customer $customer, ServiceRequest $serviceRequest, array $attributes): Feedback
    {
        if ($serviceRequest->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Service request does not belong to the customer.');
        }

        $feedback = DB::transaction(function () use ($serviceRequest, $attributes): Feedback {
            return Feedback::query()->create([
                'service_request_id' => $serviceRequest->id,
                'score' => $attributes['score'],
                'comment' => $attributes['comment'] ?? null,
            ]);
        });

        FeedbackSubmitted::dispatch((int) $feedback->id);

        return $feedback;
    }
}
