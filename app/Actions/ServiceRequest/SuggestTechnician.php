<?php

declare(strict_types=1);

namespace App\Actions\ServiceRequest;

use App\Models\ServiceRequest;
use App\Models\Technician;
use App\Services\Technician\TechnicianSuggestionService;
use Illuminate\Database\Eloquent\Collection;

final class SuggestTechnician
{
    public function __construct(
        private readonly TechnicianSuggestionService $suggestions,
    ) {}

    /**
     * @return Collection<int, Technician>
     */
    public function execute(ServiceRequest $serviceRequest): Collection
    {
        return $this->suggestions->suggest($serviceRequest);
    }
}
