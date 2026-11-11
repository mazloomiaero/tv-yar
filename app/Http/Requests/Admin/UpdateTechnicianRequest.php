<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Branch;
use App\Models\Skill;
use App\Models\Technician;
use App\Models\User;
use App\Support\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTechnicianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $technician = $this->route('technician');

        return $user !== null
            && $user->hasRole(RoleName::CompanyAdmin)
            && $technician instanceof Technician;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $technician = $this->route('technician');
        $userId = $technician instanceof Technician ? (int) $technician->user_id : 0;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'string',
                'size:11',
                'regex:/^09\d{9}$/',
                Rule::unique(User::class, 'username')->ignore($userId > 0 ? $userId : null),
            ],
            'branch_id' => [
                'required',
                'integer',
                Rule::exists(Branch::class, 'id')->whereNull('deleted_at'),
            ],
            'password' => [
                Rule::requiredIf(fn (): bool => ! $technician instanceof Technician || $technician->user_id === null),
                'nullable',
                'string',
                'min:4',
                'confirmed',
            ],
            'skill_ids' => ['sometimes', 'array'],
            'skill_ids.*' => ['integer', Rule::exists(Skill::class, 'id')->whereNull('deleted_at')],
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
            'mobile' => 'شماره همراه',
            'branch_id' => 'شعبه',
            'password' => 'رمز عبور',
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     first_name: string,
     *     last_name: string,
     *     mobile: string,
     *     username: string,
     *     branch_id: int,
     *     password: ?string,
     *     skill_ids: list<int>
     * }
     */
    public function catalogPayload(): array
    {
        $validated = $this->validated();

        return [
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'mobile' => $validated['mobile'],
            'username' => $validated['mobile'],
            'branch_id' => (int) $validated['branch_id'],
            'password' => isset($validated['password']) && $validated['password'] !== ''
                ? $validated['password']
                : null,
            'skill_ids' => array_map('intval', $validated['skill_ids'] ?? []),
        ];
    }
}
