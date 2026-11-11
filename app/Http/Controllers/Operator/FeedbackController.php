<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FeedbackController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', Feedback::class);

        $feedbacks = Feedback::query()
            ->with('serviceRequest')
            ->orderByDesc('id')
            ->get();

        return view('operator.feedback.index', [
            'title' => 'Feedback',
            'feedbacks' => $feedbacks,
        ]);
    }

    public function show(Feedback $feedback): View
    {
        $this->authorize('view', $feedback);

        $feedback->load('serviceRequest.customer');

        return view('operator.feedback.show', [
            'title' => 'Feedback',
            'feedback' => $feedback,
        ]);
    }
}
