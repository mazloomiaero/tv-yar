<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Brand;
use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $brand = $this->route('brand');

        return $user !== null
            && $user->hasRole(RoleName::CompanyAdmin)
            && $brand instanceof Brand;
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
