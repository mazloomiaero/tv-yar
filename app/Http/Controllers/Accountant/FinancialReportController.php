<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FinancialReportController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(ReportService $reports): View
    {
        $this->authorize('viewAny', Invoice::class);
        $this->authorize('viewAny', Payment::class);

        return view('accountant.financial-report', [
            'title' => 'Financial report',
            'report' => $reports->financialReport(),
        ]);
    }
}
