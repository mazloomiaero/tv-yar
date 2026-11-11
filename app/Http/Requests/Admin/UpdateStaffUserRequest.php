<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $staff = $this->route('staff');

        return $user !== null
            && $user->hasRole(RoleName::CompanyAdmin)
            && $staff instanceof User;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $staff = $this->route('staff');
        $staffId = $staff instanceof User ? $staff->id : 0;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'size:11', 'regex:/^09\d{9}$/'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class, 'username')->ignore($staffId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique(User::class, 'email')->ignore($staffId),
            ],
            'password' => ['nullable', 'string', 'min:4', 'confirmed'],
            'role' => ['required', 'string', Rule::in($this->assignableRoles())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.size' => 'تلفن همراه باید دقیقاً ۱۱ رقم باشد.',
            'mobile.regex' => 'تلفن همراه باید عدد باشد و با ۰۹ شروع شود.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'mobile' => 'تلفن همراه',
            'username' => 'نام کاربری',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'role' => 'نقش',
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     first_name: string,
     *     last_name: string,
     *     mobile: string,
     *     username: string,
     *     email: ?string,
     *     password: ?string,
     *     role: RoleName
     * }
     */
    public function staffPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'mobile' => $validated['mobile'],
            'username' => $validated['username'],
            'email' => isset($validated['email']) && $validated['email'] !== '' ? $validated['email'] : null,
            'password' => isset($validated['password']) && $validated['password'] !== ''
                ? $validated['password']
                : null,
            'role' => RoleName::from($validated['role']),
        ];
    }

    /**
     * @return list<string>
     */
    private function assignableRoles(): array
    {
        return [
            RoleName::CompanyAdmin->value,
            RoleName::Operator->value,
            RoleName::TechnicalSupervisor->value,
            RoleName::Accountant->value,
        ];
    }
}
