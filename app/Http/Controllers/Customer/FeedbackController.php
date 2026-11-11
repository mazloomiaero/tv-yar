<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Feedback\CreateFeedback;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreFeedbackRequest;
use App\Models\Customer;
use App\Models\ServiceRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class FeedbackController extends Controller
{
    public function create(Request $request, ServiceRequest $serviceRequest): View
    {
        $this->ownServiceRequest($request->user('customer'), $serviceRequest);

        $serviceRequest->load('feedbacks');

        return view('customer.feedback.create', [
            'title' => 'Feedback '.$serviceRequest->request_number,
            'serviceRequest' => $serviceRequest,
        ]);
    }

    public function store(
        StoreFeedbackRequest $request,
        ServiceRequest $serviceRequest,
        CreateFeedback $createFeedback,
    ): RedirectResponse {
        $customer = $this->ownServiceRequest($request->user('customer'), $serviceRequest);

        try {
            $createFeedback->execute($customer, $serviceRequest, $request->payload());
        } catch (InvalidArgumentException $exception) {
            abort(404);
        }

        return redirect()
            ->route('customer.service-requests.feedback.create', $serviceRequest)
            ->with('status', 'Feedback submitted.');
    }

    private function ownServiceRequest(mixed $customer, ServiceRequest $serviceRequest): Customer
    {
        if (! $customer instanceof Customer || $serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        return $customer;
    }
}
