<?php

declare(strict_types=1);

namespace App\Http\Controllers\Technician;

use App\Actions\ServiceReport\CreateServiceReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\StoreServiceReportRequest;
use App\Models\Part;
use App\Models\ServiceAssignment;
use App\Models\ServiceReport;
use App\Models\Technician;
use App\Models\User;
use App\Support\ServiceReportImageKind;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceReportController extends Controller
{
    use AuthorizesRequests;

    public function create(Request $request, ServiceAssignment $assignment): View
    {
        $this->assertOwnAssignment($request, $assignment);
        $this->authorize('create', [ServiceReport::class, $assignment->serviceRequest]);

        return view('technician.reports.create', [
            'title' => 'Create service report',
            'assignment' => $assignment,
            'serviceRequest' => $assignment->serviceRequest,
            'parts' => Part::query()->orderBy('name')->get(),
            'imageKinds' => ServiceReportImageKind::cases(),
        ]);
    }

    public function store(
        StoreServiceReportRequest $request,
        ServiceAssignment $assignment,
        CreateServiceReport $createServiceReport,
    ): RedirectResponse {
        $this->assertOwnAssignment($request, $assignment);

        $report = $createServiceReport->execute(
            $assignment->serviceRequest,
            $request->partLines(),
            $request->imagesByKind(),
        );

        return redirect()
            ->route('technician.reports.show', $report)
            ->with('status', 'Service report created.');
    }

    public function show(Request $request, ServiceReport $report): View
    {
        $this->authorize('view', $report);

        $report->load(['parts', 'images', 'serviceRequest']);

        return view('technician.reports.show', [
            'title' => 'Service report',
            'report' => $report,
        ]);
    }

    private function assertOwnAssignment(Request $request, ServiceAssignment $assignment): void
    {
        $user = $request->user('web');
        $technician = Technician::forUser($user instanceof User ? $user : null);

        if (! $technician instanceof Technician || $assignment->technician_id !== $technician->id) {
            abort(404);
        }

        $assignment->loadMissing('serviceRequest');
    }
}
