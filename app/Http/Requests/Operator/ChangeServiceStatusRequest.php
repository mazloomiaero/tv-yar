<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ChangeServiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $serviceRequest = $this->route('serviceRequest');

        return $user !== null
            && $serviceRequest instanceof ServiceRequest
            && $user->can('changeStatus', $serviceRequest);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status_id' => ['required', 'integer', Rule::exists(ServiceStatus::class, 'id')->whereNull('deleted_at')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            if (! $serviceRequest instanceof ServiceRequest) {
                return;
            }

            $statusId = (int) $this->input('status_id');
            if ($statusId < 1) {
                return;
            }

            $mapped = $serviceRequest->serviceType
                ->statuses()
                ->where('service_statuses.id', $statusId)
                ->exists();

            if (! $mapped) {
                $validator->errors()->add(
                    'status_id',
                    'The status is not allowed for this service type.',
                );
            }
        });
    }
}
