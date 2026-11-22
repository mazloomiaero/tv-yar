<?php

declare(strict_types=1);

namespace App\Http\Requests\Intake;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebsiteIntakeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:200'],
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'brand' => ['required', 'string', 'max:150'],
            'problem' => ['required', 'string', 'max:5000'],
            'address' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'نام و نام خانوادگی الزامی است.',
            'mobile.required' => 'شماره همراه الزامی است.',
            'mobile.regex' => 'شماره همراه باید با ۰۹ و ۱۱ رقم باشد.',
            'brand.required' => 'برند دستگاه الزامی است.',
            'problem.required' => 'شرح ایراد الزامی است.',
            'address.required' => 'آدرس الزامی است.',
        ];
    }
}
