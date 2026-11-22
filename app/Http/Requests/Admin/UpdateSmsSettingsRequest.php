<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSmsSettingsRequest extends FormRequest
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
            'api_key' => ['nullable', 'string', 'max:255'],
            'pattern_code' => ['nullable', 'string', 'max:100'],
            'otp_param' => ['nullable', 'string', 'max:50'],
            'line_number' => ['nullable', 'string', 'max:30'],
        ];
    }
}
