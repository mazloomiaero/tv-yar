<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\DeviceType;
use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $deviceType = $this->route('device_type');

        return $user !== null
            && $user->hasRole(RoleName::CompanyAdmin)
            && $deviceType instanceof DeviceType;
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
