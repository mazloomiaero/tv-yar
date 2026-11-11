<?php

declare(strict_types=1);

namespace App\Http\Controllers\Technical;

use App\Actions\ServiceRequest\AssignTechnician;
use App\Actions\ServiceRequest\SuggestTechnician;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest\AssignTechnicianRequest;
use App\Http\Requests\ServiceRequest\FilterAssignmentCalendarRequest;
use App\Models\Branch;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Models\User;
use App\Services\CustomField\CustomFieldService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class ServiceRequestController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $serviceRequests = ServiceRequest::query()
            ->with(['customer', 'device', 'serviceType', 'status'])
            ->orderByDesc('id')
            ->get();

        return view('technical.service-requests.index', [
            'title' => 'Service requests',
            'serviceRequests' => $serviceRequests,
        ]);
    }

    public function show(
        ServiceRequest $serviceRequest,
        CustomFieldService $customFields,
        SuggestTechnician $suggestTechnician,
    ): View {
        $this->authorize('view', $serviceRequest);

        $serviceRequest->load([
            'customer',
            'device.deviceType',
            'device.brand',
            'serviceType',
            'address',
            'status',
            'assignments.technician',
            'assignments.branch',
            'reception.receivedBy',
        ]);

        return view('technical.service-requests.show', [
            'title' => 'Service request '.$serviceRequest->request_number,
            'serviceRequest' => $serviceRequest,
            'customFieldValues' => $customFields->valuesForServiceRequest((int) $serviceRequest->id),
            'customFields' => $customFields->fieldsForServiceType((int) $serviceRequest->service_type_id),
            'suggestions' => $suggestTechnician->execute($serviceRequest),
            'technicians' => Technician::query()->with('branch')->orderBy('name')->get(),
            'branches' => Branch::query()->orderBy('name')->get(),
            'assignRoute' => 'technical.service-requests.assignments.store',
        ]);
    }

    public function assign(
        AssignTechnicianRequest $request,
        ServiceRequest $serviceRequest,
        AssignTechnician $assignTechnician,
    ): RedirectResponse {
        $user = $request->user('web');
        if (! $user instanceof User) {
            abort(403);
        }

        $assignTechnician->execute(
            $serviceRequest,
            $user,
            $request->assignmentAttributes(),
        );

        return redirect()
            ->route('technical.service-requests.show', $serviceRequest)
            ->with('status', 'Technician assigned.');
    }

    public function calendar(FilterAssignmentCalendarRequest $request): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $scheduledDate = $request->scheduledDate();

        $assignments = ServiceAssignment::query()
            ->with(['technician', 'serviceRequest', 'branch'])
            ->scheduledOn($scheduledDate)
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->get();

        return view('assignments.calendar', [
            'title' => 'Assignment calendar',
            'layout' => 'layouts.technical',
            'assignments' => $assignments,
            'scheduledDate' => $scheduledDate,
            'filterRoute' => 'technical.calendar',
            'requestRoute' => 'technical.service-requests.show',
        ]);
    }
}
