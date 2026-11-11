<?php

declare(strict_types=1);

namespace App\Http\Requests\Operator\Concerns;

use App\Services\CustomField\CustomFieldService;
use Illuminate\Validation\Validator;

trait ValidatesDeviceCustomFieldValues
{
    protected function validateMappedCustomFieldValues(Validator $validator): void
    {
        $deviceTypeId = (int) $this->input('device_type_id');
        if ($deviceTypeId < 1) {
            return;
        }

        $values = $this->input('custom_field_values', []);
        if (! is_array($values)) {
            return;
        }

        $fields = app(CustomFieldService::class)->fieldsForDeviceType($deviceTypeId);

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
