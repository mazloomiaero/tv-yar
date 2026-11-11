<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CloseServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $serviceRequest = $this->route('serviceRequest');

        return $user !== null
            && $serviceRequest instanceof ServiceRequest
            && $user->can('close', $serviceRequest);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $serviceRequest = $this->route('serviceRequest');
            if ($serviceRequest instanceof ServiceRequest && $serviceRequest->isClosed()) {
                $validator->errors()->add('service_request', 'This service request is already closed.');
            }
        });
    }
}
