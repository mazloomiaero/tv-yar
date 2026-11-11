<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceRequest;

use App\Models\Branch;
use App\Models\ServiceRequest;
use App\Models\Technician;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AssignTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $serviceRequest = $this->route('serviceRequest');

        return $user !== null
            && $serviceRequest instanceof ServiceRequest
            && $user->can('assign', $serviceRequest);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'technician_id' => [
                'required',
                'integer',
                Rule::exists(Technician::class, 'id')->whereNull('deleted_at'),
            ],
            'branch_id' => [
                'nullable',
                'integer',
                Rule::exists(Branch::class, 'id')->whereNull('deleted_at'),
            ],
            'scheduled_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            if ($serviceRequest instanceof ServiceRequest && $serviceRequest->isClosed()) {
                $validator->errors()->add(
                    'service_request',
                    'Cannot assign a closed service request.',
                );
            }
        });
    }

    /**
     * @return array{
     *     technician_id: int,
     *     branch_id: int|null,
     *     scheduled_date: string,
     *     start_time: string,
     *     end_time: string
     * }
     */
    public function assignmentAttributes(): array
    {
        $validated = $this->validated();
        $start = (string) $validated['start_time'];
        $end = (string) $validated['end_time'];

        return [
            'technician_id' => (int) $validated['technician_id'],
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            'scheduled_date' => (string) $validated['scheduled_date'],
            'start_time' => strlen($start) === 5 ? $start.':00' : $start,
            'end_time' => strlen($end) === 5 ? $end.':00' : $end,
        ];
    }
}
