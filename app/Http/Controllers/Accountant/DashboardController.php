<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports): View
    {
        return view('accountant.dashboard', [
            'title' => 'Accountant',
            'invoiceCountsByStatus' => $reports->invoiceCountsByStatus(),
            'paymentCountsByMethod' => $reports->paymentCountsByMethod(),
            'paymentCountsByStatus' => $reports->paymentCountsByStatus(),
            'invoices' => $reports->invoices(),
            'payments' => $reports->payments(),
        ]);
    }
}
