<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('customer') instanceof Customer;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'score' => ['required', 'string', 'max:50'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array{score: string, comment: string|null}
     */
    public function payload(): array
    {
        $validated = $this->validated();

        return [
            'score' => $validated['score'],
            'comment' => $validated['comment'] ?? null,
        ];
    }
}
