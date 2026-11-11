<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ServiceStatus;
use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $serviceStatus = $this->route('service_status');

        return $user !== null
            && $user->hasRole(RoleName::CompanyAdmin)
            && $serviceStatus instanceof ServiceStatus;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
        ];
    }
}
