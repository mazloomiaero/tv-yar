<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceAssignment;
use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Support\AssignmentInsertDefaults;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceAssignment>
 */
class ServiceAssignmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'branch_id' => null,
            'technician_id' => Technician::factory(),
            'is_suggested' => false,
            'assigned_by' => null,
            'scheduled_date' => '2026-09-12',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'status' => AssignmentInsertDefaults::STATUS,
            'technician_response' => AssignmentInsertDefaults::TECHNICIAN_RESPONSE,
            'accepted_at' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}
