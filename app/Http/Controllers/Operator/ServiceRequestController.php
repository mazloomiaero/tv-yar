<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operator;

use App\Actions\ServiceRequest\ChangeServiceStatus;
use App\Actions\ServiceRequest\CloseServiceRequest;
use App\Actions\ServiceRequest\CreateServiceRequest;
use App\Actions\ServiceRequest\SuggestTechnician;
use App\Http\Controllers\Controller;
use App\Http\Requests\Operator\CancelServiceRequestRequest;
use App\Http\Requests\Operator\ChangeServiceStatusRequest;
use App\Http\Requests\Operator\CloseServiceRequestRequest;
use App\Http\Requests\Operator\StoreServiceRequestRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeviceType;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Models\Technician;
use App\Models\User;
use App\Services\CustomField\CustomFieldService;
use App\Support\CancelledServiceStatus;
use App\Support\OpsRoute;
use App\Support\ServiceRequestSource;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class ServiceRequestController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ServiceRequest::class);

        $createdFilter = (string) $request->query('created', '');

        $serviceRequests = ServiceRequest::query()
            ->with(['customer', 'device', 'serviceType', 'status'])
            ->when(
                $createdFilter === 'today',
                fn ($query) => $query->whereDate('created_at', now()->toDateString()),
            )
            ->orderByDesc('id')
            ->get();

        return view('operator.service-requests.index', [
            'title' => 'Service requests',
            'serviceRequests' => $serviceRequests,
            'createdFilter' => $createdFilter,
        ]);
    }

    public function create(Customer $customer, CustomFieldService $customFields): View
    {
        $this->authorize('create', ServiceRequest::class);

        $customer->load(['devices.deviceType', 'addresses']);

        $deviceTypeIds = $customer->devices->pluck('device_type_id')->unique()->filter()->all();
        $serviceTypesByDeviceType = DeviceType::query()
            ->whereIn('id', $deviceTypeIds)
            ->with(['serviceTypes' => fn ($query) => $query->orderBy('name')])
            ->get()
            ->mapWithKeys(
                fn (DeviceType $deviceType) => [$deviceType->id => $deviceType->serviceTypes],
            );

        return view('operator.service-requests.create', [
            'title' => 'Create service request',
            'customer' => $customer,
            'devices' => $customer->devices,
            'addresses' => $customer->addresses,
            'serviceTypesByDeviceType' => $serviceTypesByDeviceType,
            'sources' => ServiceRequestSource::cases(),
            'customFieldsByServiceType' => $customFields->fieldsGroupedByServiceType(),
        ]);
    }

    public function store(
        StoreServiceRequestRequest $request,
        Customer $customer,
        CreateServiceRequest $createServiceRequest,
    ): RedirectResponse {
        $validated = $request->validated();
        $operator = $request->user('web');

        if (! $operator instanceof User) {
            abort(403);
        }

        $serviceRequest = $createServiceRequest->execute(
            $customer,
            $operator,
            $this->createAttributes($validated),
            $this->customFieldValues($validated),
            (int) config('document.year'),
        );

        return redirect()
            ->route(OpsRoute::name('service-requests.show'), $serviceRequest)
            ->with('status', 'Service request created.');
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
            'createdBy',
            'statusHistory.status',
            'statusHistory.changedBy',
            'assignments.technician',
            'assignments.branch',
        ]);

        $allowedStatuses = $serviceRequest->serviceType
            ->statuses()
            ->orderBy('name')
            ->get();

        return view('operator.service-requests.show', [
            'title' => 'Service request '.$serviceRequest->request_number,
            'serviceRequest' => $serviceRequest,
            'allowedStatuses' => $allowedStatuses,
            'customFieldValues' => $customFields->valuesForServiceRequest((int) $serviceRequest->id),
            'customFields' => $customFields->fieldsForServiceType((int) $serviceRequest->service_type_id),
            'suggestions' => $suggestTechnician->execute($serviceRequest),
            'technicians' => Technician::query()->with('branch')->orderBy('name')->get(),
            'branches' => Branch::query()->orderBy('name')->get(),
            'assignRoute' => OpsRoute::name('service-requests.assignments.store'),
        ]);
    }

    public function changeStatus(
        ChangeServiceStatusRequest $request,
        ServiceRequest $serviceRequest,
        ChangeServiceStatus $changeServiceStatus,
    ): RedirectResponse {
        $operator = $request->user('web');
        if (! $operator instanceof User) {
            abort(403);
        }

        $status = ServiceStatus::query()->findOrFail((int) $request->validated('status_id'));
        $changeServiceStatus->execute($serviceRequest, $status, $operator);

        return redirect()
            ->route(OpsRoute::name('service-requests.show'), $serviceRequest)
            ->with('status', 'Service status updated.');
    }

    public function close(
        CloseServiceRequestRequest $request,
        ServiceRequest $serviceRequest,
        CloseServiceRequest $closeServiceRequest,
    ): RedirectResponse {
        $closeServiceRequest->execute($serviceRequest);

        return redirect()
            ->route(OpsRoute::name('service-requests.show'), $serviceRequest)
            ->with('status', 'Service request closed.');
    }

    public function cancel(
        CancelServiceRequestRequest $request,
        ServiceRequest $serviceRequest,
        ChangeServiceStatus $changeServiceStatus,
        CloseServiceRequest $closeServiceRequest,
    ): RedirectResponse {
        $operator = $request->user('web');
        if (! $operator instanceof User) {
            abort(403);
        }

        $status = ServiceStatus::findByCode(CancelledServiceStatus::CODE);

        if (! $status instanceof ServiceStatus) {
            throw new RuntimeException(
                'Seeded status ['.CancelledServiceStatus::CODE.'] is missing.',
            );
        }

        try {
            $changeServiceStatus->execute($serviceRequest, $status, $operator);
        } catch (InvalidArgumentException) {
            return redirect()
                ->route(OpsRoute::name('service-requests.index'))
                ->withErrors(['service_request' => 'وضعیت لغو برای این نوع خدمت تعریف نشده است.']);
        }

        $serviceRequest->refresh();

        if (! $serviceRequest->isClosed()) {
            $closeServiceRequest->execute($serviceRequest);
        }

        return redirect()
            ->route(OpsRoute::name('service-requests.index'))
            ->with('status', 'درخواست لغو شد.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *     device_id: int,
     *     service_type_id: int,
     *     address_id: int|null,
     *     create_address: bool,
     *     source: string,
     *     priority: string,
     *     problem_description: string,
     *     preferred_date: string,
     *     preferred_start_time: string,
     *     preferred_end_time: string,
     *     notes: string
     * }
     */
    private function createAttributes(array $validated): array
    {
        $start = (string) $validated['preferred_start_time'];
        $end = (string) $validated['preferred_end_time'];

        return [
            'device_id' => (int) $validated['device_id'],
            'service_type_id' => (int) $validated['service_type_id'],
            'address_id' => isset($validated['address_id']) ? (int) $validated['address_id'] : null,
            'create_address' => (bool) ($validated['create_address'] ?? false),
            'source' => (string) $validated['source'],
            'priority' => (string) $validated['priority'],
            'problem_description' => (string) $validated['problem_description'],
            'preferred_date' => (string) $validated['preferred_date'],
            'preferred_start_time' => strlen($start) === 5 ? $start.':00' : $start,
            'preferred_end_time' => strlen($end) === 5 ? $end.':00' : $end,
            'notes' => (string) ($validated['notes'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<int, string>
     */
    private function customFieldValues(array $validated): array
    {
        $raw = $validated['custom_field_values'] ?? [];
        if (! is_array($raw)) {
            return [];
        }

        $values = [];
        foreach ($raw as $fieldId => $value) {
            $values[(int) $fieldId] = is_string($value) ? $value : (string) $value;
        }

        return $values;
    }
}
