<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;

class CancelServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $serviceRequest = $this->route('serviceRequest');

        return $user !== null
            && $serviceRequest instanceof ServiceRequest
            && $user->can('changeStatus', $serviceRequest)
            && ! $serviceRequest->isClosed();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
