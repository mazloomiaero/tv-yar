<?php

declare(strict_types=1);

namespace App\Http\Controllers\Technical;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): View
    {
        $entityCounts = $reports->entityCounts();

        return view('technical.dashboard', [
            'title' => 'Technical Supervisor',
            'requestCount' => $entityCounts['service_requests'],
            'assignmentCount' => $entityCounts['service_assignments'],
            'receptionCount' => $entityCounts['device_receptions'],
            'reportCount' => $entityCounts['service_reports'],
            'assignmentSuggestionCounts' => $reports->assignmentSuggestionCounts(),
            'serviceRequests' => $reports->serviceRequests(),
        ]);
    }
}
