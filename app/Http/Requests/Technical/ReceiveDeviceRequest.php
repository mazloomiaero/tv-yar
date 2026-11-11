<?php

declare(strict_types=1);

namespace App\Http\Requests\Technical;

use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Support\WorkshopServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReceiveDeviceRequest extends FormRequest
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
            'received_date' => ['required', 'date'],
            'appearance' => ['nullable', 'string', 'max:5000'],
            'accompanying_items' => ['nullable', 'string', 'max:5000'],
            'reason' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            if (! $serviceRequest instanceof ServiceRequest) {
                return;
            }

            if ($serviceRequest->reception()->exists()) {
                $validator->errors()->add(
                    'received_date',
                    'This service request already has a reception receipt.',
                );
            }

            $status = ServiceStatus::query()
                ->where('name', WorkshopServiceStatus::DeviceReceived)
                ->first();

            if (! $status instanceof ServiceStatus) {
                $validator->errors()->add(
                    'received_date',
                    'Status ['.WorkshopServiceStatus::DeviceReceived.'] is not seeded.',
                );

                return;
            }

            $mapped = $serviceRequest->serviceType
                ->statuses()
                ->where('service_statuses.id', $status->id)
                ->exists();

            if (! $mapped) {
                $validator->errors()->add(
                    'received_date',
                    'The status is not allowed for this service type.',
                );
            }
        });
    }

    /**
     * @return array{
     *     received_date: string,
     *     appearance: string,
     *     accompanying_items: string,
     *     reason: string,
     *     notes: string
     * }
     */
    public function receptionAttributes(): array
    {
        $validated = $this->validated();

        return [
            'received_date' => (string) $validated['received_date'],
            'appearance' => (string) ($validated['appearance'] ?? ''),
            'accompanying_items' => (string) ($validated['accompanying_items'] ?? ''),
            'reason' => (string) ($validated['reason'] ?? ''),
            'notes' => (string) ($validated['notes'] ?? ''),
        ];
    }
}
