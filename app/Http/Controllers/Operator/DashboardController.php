<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): View
    {
        $entityCounts = $reports->entityCounts();

        return view('operator.dashboard', [
            'title' => 'Operator',
            'customerCount' => $entityCounts['customers'],
            'deviceCount' => $entityCounts['devices'],
            'requestCount' => $entityCounts['service_requests'],
            'assignmentCount' => $entityCounts['service_assignments'],
            'feedbackCount' => $entityCounts['feedbacks'],
            'requestCountsBySource' => $reports->requestCountsBySource(),
            'assignmentSuggestionCounts' => $reports->assignmentSuggestionCounts(),
            'serviceRequests' => $reports->serviceRequests(),
        ]);
    }
}
