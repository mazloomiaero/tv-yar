<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
        ];
    }
}
