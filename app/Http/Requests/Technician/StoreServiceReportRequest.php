<?php

declare(strict_types=1);

namespace App\Http\Requests\Technician;

use App\Models\Part;
use App\Models\ServiceAssignment;
use App\Models\ServiceReport;
use App\Models\Technician;
use App\Models\User;
use App\Support\ServiceReportImageKind;
use App\Support\ServiceReportImageRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServiceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user('web');
        $assignment = $this->route('assignment');

        if (! $user instanceof User || ! $assignment instanceof ServiceAssignment) {
            return false;
        }

        $technician = Technician::forUser($user);
        if (! $technician instanceof Technician || $assignment->technician_id !== $technician->id) {
            abort(404);
        }

        $serviceRequest = $assignment->serviceRequest;
        if ($serviceRequest === null) {
            return false;
        }

        return $user->can('create', [ServiceReport::class, $serviceRequest]);
    }

    protected function prepareForValidation(): void
    {
        $description = $this->input('description');
        if (is_string($description)) {
            $this->merge(['description' => trim($description)]);
        }

        $parts = [];

        foreach ((array) $this->input('parts', []) as $line) {
            if (! is_array($line)) {
                continue;
            }

            $partId = trim((string) ($line['part_id'] ?? ''));
            $name = trim((string) ($line['name'] ?? ''));
            $quantity = trim((string) ($line['quantity'] ?? ''));
            $unitPrice = trim((string) ($line['unit_price'] ?? ''));

            if ($partId === '' && $name === '' && $quantity === '' && $unitPrice === '') {
                continue;
            }

            $parts[] = $line;
        }

        $this->merge(['parts' => $parts]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $imageRule = [
            'file',
            'image',
            'mimes:'.implode(',', ServiceReportImageRules::extensions()),
            'max:'.ServiceReportImageRules::MAX_KILOBYTES,
        ];

        return [
            'description' => ['required', 'string', 'min:10'],
            'parts' => ['sometimes', 'array'],
            'parts.*.part_id' => [
                'nullable',
                'integer',
                Rule::exists(Part::class, 'id')->whereNull('deleted_at'),
            ],
            'parts.*.name' => ['nullable', 'string', 'max:150'],
            'parts.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0'],
            'parts.*.unit_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'images' => ['sometimes', 'array'],
            'images.problems' => ['sometimes', 'array'],
            'images.problems.*' => $imageRule,
            'images.before' => ['sometimes', 'array'],
            'images.before.*' => $imageRule,
            'images.after' => ['sometimes', 'array'],
            'images.after.*' => $imageRule,
            'images.damaged-parts' => ['sometimes', 'array'],
            'images.damaged-parts.*' => $imageRule,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'شرح گزارش الزامی است.',
            'description.min' => 'شرح گزارش باید حداقل ۱۰ نویسه باشد.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ((array) $this->input('parts', []) as $index => $line) {
                if (! is_array($line)) {
                    continue;
                }

                $partId = (int) ($line['part_id'] ?? 0);
                $name = trim((string) ($line['name'] ?? ''));
                $unitPrice = $line['unit_price'] ?? null;

                if ($partId < 1 && $name === '') {
                    $validator->errors()->add(
                        'parts.'.$index.'.name',
                        'A name is required for a free-text part.',
                    );
                }

                if ($partId < 1 && ($unitPrice === null || $unitPrice === '')) {
                    $validator->errors()->add(
                        'parts.'.$index.'.unit_price',
                        'A unit price is required for a free-text part.',
                    );
                }
            }
        });
    }

    public function reportDescription(): string
    {
        return (string) $this->validated('description');
    }

    /**
     * @return list<array{part_id?: int|null, name?: string|null, quantity: string, unit_price?: string|null}>
     */
    public function partLines(): array
    {
        $lines = [];

        foreach ((array) $this->validated('parts', []) as $line) {
            if (! is_array($line)) {
                continue;
            }

            $partId = isset($line['part_id']) && $line['part_id'] !== null && $line['part_id'] !== ''
                ? (int) $line['part_id']
                : null;

            $lines[] = [
                'part_id' => $partId,
                'name' => isset($line['name']) ? (string) $line['name'] : null,
                'quantity' => (string) $line['quantity'],
                'unit_price' => isset($line['unit_price']) && $line['unit_price'] !== null && $line['unit_price'] !== ''
                    ? (string) $line['unit_price']
                    : null,
            ];
        }

        return $lines;
    }

    /**
     * @return array<string, list<UploadedFile>>
     */
    public function imagesByKind(): array
    {
        $grouped = [];

        foreach (ServiceReportImageKind::values() as $kind) {
            $files = $this->file('images.'.$kind);
            if (! is_array($files)) {
                continue;
            }

            $uploaded = array_values(array_filter(
                $files,
                fn (mixed $file): bool => $file instanceof UploadedFile,
            ));

            if ($uploaded !== []) {
                $grouped[$kind] = $uploaded;
            }
        }

        return $grouped;
    }
}
