<?php

declare(strict_types=1);

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): View
    {
        $technician = Technician::forUser($request->user('web'));
        $assignments = $reports->assignmentsForTechnician($technician);

        return view('technician.dashboard', [
            'title' => 'Technician',
            'assignmentCount' => $assignments->count(),
            'suggestedCount' => $assignments->where('is_suggested', true)->count(),
            'assignments' => $assignments,
        ]);
    }
}
