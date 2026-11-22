<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthBrandingRequest extends FormRequest
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
            'kicker' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:150'],
            'lead' => ['required', 'string', 'max:500'],
            'trust_1' => ['required', 'string', 'max:150'],
            'trust_2' => ['required', 'string', 'max:150'],
            'trust_3' => ['required', 'string', 'max:150'],
        ];
    }
}
