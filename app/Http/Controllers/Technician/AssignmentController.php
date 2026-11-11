<?php

declare(strict_types=1);

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest\FilterAssignmentCalendarRequest;
use App\Models\ServiceAssignment;
use App\Models\Technician;
use App\Services\CustomField\CustomFieldService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(FilterAssignmentCalendarRequest $request): View
    {
        $scheduledDate = $request->scheduledDate();
        $technician = Technician::forUser($request->user('web'));

        $assignments = $technician instanceof Technician
            ? ServiceAssignment::query()
                ->with(['serviceRequest', 'branch'])
                ->where('technician_id', $technician->id)
                ->scheduledOn($scheduledDate)
                ->orderBy('scheduled_date')
                ->orderBy('start_time')
                ->get()
            : ServiceAssignment::query()->whereKey([])->get();

        return view('technician.assignments.index', [
            'title' => 'My assignments',
            'assignments' => $assignments,
            'scheduledDate' => $scheduledDate,
        ]);
    }

    public function show(Request $request, ServiceAssignment $assignment, CustomFieldService $customFields): View
    {
        $technician = Technician::forUser($request->user('web'));
        if (! $technician instanceof Technician || $assignment->technician_id !== $technician->id) {
            abort(404);
        }

        $assignment->load([
            'serviceRequest.customer',
            'serviceRequest.device.deviceType',
            'serviceRequest.device.brand',
            'serviceRequest.serviceType',
            'serviceRequest.status',
            'serviceRequest.reports',
            'serviceRequest.invoices',
            'branch',
        ]);

        $serviceRequest = $assignment->serviceRequest;

        return view('technician.assignments.show', [
            'title' => 'Assignment '.$serviceRequest?->request_number,
            'assignment' => $assignment,
            'serviceRequest' => $serviceRequest,
            'customFieldValues' => $serviceRequest
                ? $customFields->valuesForServiceRequest((int) $serviceRequest->id)
                : [],
            'customFields' => $serviceRequest
                ? $customFields->fieldsForServiceType((int) $serviceRequest->service_type_id)
                : collect(),
        ]);
    }
}
