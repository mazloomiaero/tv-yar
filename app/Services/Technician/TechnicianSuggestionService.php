<?php

declare(strict_types=1);

namespace App\Services\Technician;

use App\Models\ServiceRequest;
use App\Models\Technician;
use Illuminate\Database\Eloquent\Collection;

final class TechnicianSuggestionService
{
    /**
     * Display-only ranking. There is no skill–service_type table; a technician
     * is suggested when a skill name matches the service type name or a stored
     * service-request custom field value. Open assignments are those with
     * completed_at IS NULL. The caller still chooses the technician.
     *
     * @return Collection<int, Technician>
     */
    public function suggest(ServiceRequest $serviceRequest): Collection
    {
        $tokens = $this->matchTokens($serviceRequest);

        $technicians = Technician::query()
            ->with(['skills', 'branch'])
            ->withCount([
                'assignments as open_assignments_count' => fn ($query) => $query->whereNull('completed_at'),
            ])
            ->orderBy('name')
            ->get();

        return $technicians
            ->filter(fn (Technician $technician): bool => $this->matchesTokens($technician, $tokens))
            ->sortBy([
                ['open_assignments_count', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    /**
     * @return list<int>
     */
    public function suggestedIds(ServiceRequest $serviceRequest): array
    {
        return $this->suggest($serviceRequest)->modelKeys();
    }

    /**
     * @return list<string>
     */
    private function matchTokens(ServiceRequest $serviceRequest): array
    {
        $serviceRequest->loadMissing(['serviceType', 'customFieldValues']);

        $tokens = [];
        $serviceTypeName = trim((string) $serviceRequest->serviceType?->name);
        if ($serviceTypeName !== '') {
            $tokens[] = mb_strtolower($serviceTypeName);
        }

        foreach ($serviceRequest->customFieldValues as $row) {
            $value = trim((string) $row->value);
            if ($value !== '') {
                $tokens[] = mb_strtolower($value);
            }
        }

        return array_values(array_unique($tokens));
    }

    /**
     * @param  list<string>  $tokens
     */
    private function matchesTokens(Technician $technician, array $tokens): bool
    {
        if ($tokens === []) {
            return false;
        }

        foreach ($technician->skills as $skill) {
            if (in_array(mb_strtolower((string) $skill->name), $tokens, true)) {
                return true;
            }
        }

        return false;
    }
}
