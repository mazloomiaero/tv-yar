<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator\Concerns;

use App\Services\CustomField\CustomFieldService;
use Illuminate\Validation\Validator;

trait ValidatesServiceRequestCustomFieldValues
{
    protected function validateMappedServiceRequestCustomFieldValues(Validator $validator): void
    {
        $serviceTypeId = (int) $this->input('service_type_id');
        if ($serviceTypeId < 1) {
            return;
        }

        $values = $this->input('custom_field_values', []);
        if (! is_array($values)) {
            return;
        }

        $fields = app(CustomFieldService::class)->fieldsForServiceType($serviceTypeId);

        foreach ($fields as $field) {
            $raw = $values[$field->id] ?? $values[(string) $field->id] ?? '';
            $value = trim(is_string($raw) ? $raw : (string) $raw);

            if ($value === '') {
                continue;
            }

            if ($field->options->isNotEmpty() && ! $field->options->contains('name', $value)) {
                $validator->errors()->add(
                    'custom_field_values.'.$field->id,
                    'The selected value is invalid.',
                );
            }
        }
    }
}
