<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceReport;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceReport>
 */
class ServiceReportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'description' => 'Completed service visit with the recorded work description.',
        ];
    }
}
