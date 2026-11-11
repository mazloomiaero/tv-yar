<?php

declare(strict_types=1);

namespace App\Http\Requests\Technical;

use App\Models\DeviceReception;
use App\Models\ServiceRequest;
use App\Models\ServiceStatus;
use App\Support\WorkshopServiceStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class DeliverDeviceRequest extends FormRequest
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
            'delivery_date' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            if (! $serviceRequest instanceof ServiceRequest) {
                return;
            }

            $reception = $serviceRequest->reception;
            if (! $reception instanceof DeviceReception) {
                $validator->errors()->add(
                    'delivery_date',
                    'This service request has no reception receipt.',
                );

                return;
            }

            if ($reception->isDelivered()) {
                $validator->errors()->add(
                    'delivery_date',
                    'This device has already been delivered.',
                );
            }

            $status = ServiceStatus::findByCode(WorkshopServiceStatus::DeliveredCode);

            if (! $status instanceof ServiceStatus) {
                $validator->errors()->add(
                    'delivery_date',
                    'Status ['.WorkshopServiceStatus::DeliveredCode.'] is not seeded.',
                );

                return;
            }

            $mapped = $serviceRequest->serviceType
                ->statuses()
                ->where('service_statuses.id', $status->id)
                ->exists();

            if (! $mapped) {
                $validator->errors()->add(
                    'delivery_date',
                    'The status is not allowed for this service type.',
                );
            }
        });
    }

    /**
     * @return array{delivery_date: string}
     */
    public function deliveryAttributes(): array
    {
        return [
            'delivery_date' => (string) $this->validated('delivery_date'),
        ];
    }
}
