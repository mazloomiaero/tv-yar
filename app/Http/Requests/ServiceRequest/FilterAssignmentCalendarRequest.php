<?php

declare(strict_types=1);

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class FilterAssignmentCalendarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('web') !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'scheduled_date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function scheduledDate(): ?string
    {
        $date = $this->validated('scheduled_date');

        return is_string($date) && $date !== '' ? $date : null;
    }
}
