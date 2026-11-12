<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): View
    {
        return view('admin.dashboard', [
            'title' => 'Admin',
            'entityCounts' => $reports->entityCounts(),
            'requestCountsBySource' => $reports->requestCountsBySource(),
            'assignmentSuggestionCounts' => $reports->assignmentSuggestionCounts(),
            'invoiceCountsByStatus' => $reports->invoiceCountsByStatus(),
            'paymentCountsByMethod' => $reports->paymentCountsByMethod(),
            'requestCountsByStatus' => $reports->requestCountsByStatusName(),
            'requestDailyCounts' => $reports->requestDailyCounts(30),
            'recentFeedbacks' => $reports->recentFeedbacks(5),
            'recentEvents' => $reports->recentDashboardEvents(6),
        ]);
    }
}
