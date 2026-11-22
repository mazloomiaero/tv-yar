<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebsiteIntakeSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');

        return $user !== null && $user->hasRole(RoleName::CompanyAdmin);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'device_type_id' => ['required', 'integer', Rule::exists('device_types', 'id')],
            'service_type_id' => ['required', 'integer', Rule::exists('service_types', 'id')],
            'actor_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
