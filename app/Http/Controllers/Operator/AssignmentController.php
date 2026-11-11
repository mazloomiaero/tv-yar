<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operator;

use App\Actions\ServiceRequest\AssignTechnician;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest\AssignTechnicianRequest;
use App\Http\Requests\ServiceRequest\FilterAssignmentCalendarRequest;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\OpsRoute;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class AssignmentController extends Controller
{
    use AuthorizesRequests;

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
            'layout' => OpsRoute::layout(),
            'assignments' => $assignments,
            'scheduledDate' => $scheduledDate,
            'filterRoute' => OpsRoute::name('calendar'),
            'requestRoute' => OpsRoute::name('service-requests.show'),
        ]);
    }

    public function store(
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
            ->route(OpsRoute::name('service-requests.show'), $serviceRequest)
            ->with('status', 'Technician assigned.');
    }
}
