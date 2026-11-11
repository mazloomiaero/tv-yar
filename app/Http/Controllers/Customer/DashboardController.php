<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): View
    {
        $customer = $request->user('customer');
        if (! $customer instanceof Customer) {
            abort(403);
        }

        return view('customer.dashboard', [
            'title' => 'My requests',
            'serviceRequests' => $reports->serviceRequestsForCustomer($customer),
            'invoices' => $reports->invoicesForCustomer($customer),
        ]);
    }
}
