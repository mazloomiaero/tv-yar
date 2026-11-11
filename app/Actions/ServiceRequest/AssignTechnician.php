<?php

declare(strict_types=1);

namespace App\Actions\ServiceRequest;

use App\Events\ServiceRequestAssigned;
use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Models\User;
use App\Services\Technician\TechnicianSuggestionService;
use App\Support\AssignmentInsertDefaults;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class AssignTechnician
{
    public function __construct(
        private readonly TechnicianSuggestionService $suggestions,
    ) {}

    /**
     * @param  array{
     *     technician_id: int,
     *     branch_id?: int|null,
     *     scheduled_date: string,
     *     start_time: string,
     *     end_time: string
     * }  $attributes
     */
    public function execute(ServiceRequest $serviceRequest, User $assignedBy, array $attributes): ServiceAssignment
    {
        if ($serviceRequest->isClosed()) {
            throw new InvalidArgumentException('Cannot assign a closed service request.');
        }

        $technician = Technician::query()->whereKey((int) $attributes['technician_id'])->first();
        if (! $technician instanceof Technician) {
            throw new InvalidArgumentException('Technician was not found.');
        }

        $assignment = DB::transaction(function () use ($serviceRequest, $assignedBy, $attributes, $technician): ServiceAssignment {
            return ServiceAssignment::query()->create([
                'service_request_id' => $serviceRequest->id,
                'branch_id' => $attributes['branch_id'] ?? null,
                'technician_id' => $technician->id,
                'is_suggested' => in_array(
                    (int) $technician->id,
                    $this->suggestions->suggestedIds($serviceRequest),
                    true,
                ),
                'assigned_by' => $assignedBy->id,
                'scheduled_date' => $attributes['scheduled_date'],
                'start_time' => $attributes['start_time'],
                'end_time' => $attributes['end_time'],
                'status' => AssignmentInsertDefaults::STATUS,
                'technician_response' => AssignmentInsertDefaults::TECHNICIAN_RESPONSE,
                'accepted_at' => null,
                'started_at' => null,
                'completed_at' => null,
            ]);
        });

        ServiceRequestAssigned::dispatch((int) $serviceRequest->id);

        return $assignment;
    }
}
