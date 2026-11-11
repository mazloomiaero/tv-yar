<?php

declare(strict_types=1);

namespace App\Services\CustomField;

use App\Models\CustomField;
use App\Models\Device;
use App\Models\DeviceCustomFieldValue;
use App\Models\DeviceType;
use App\Models\ServiceRequestCustomFieldValue;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CustomFieldService
{
    /**
     * @return Collection<int, CustomField>
     */
    public function fieldsForDeviceType(int $deviceTypeId): Collection
    {
        return CustomField::query()
            ->whereHas(
                'deviceTypes',
                fn ($query) => $query->where('device_types.id', $deviceTypeId),
            )
            ->with('options')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return BaseCollection<int, Collection<int, CustomField>>
     */
    public function fieldsGroupedByServiceType(): BaseCollection
    {
        return ServiceType::query()
            ->with(['customFields.options'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(
                fn (ServiceType $type): array => [$type->id => $type->customFields],
            );
    }

    /**
     * @return Collection<int, CustomField>
     */
    public function fieldsForServiceType(int $serviceTypeId): Collection
    {
        return CustomField::query()
            ->whereHas(
                'serviceTypes',
                fn ($query) => $query->where('service_types.id', $serviceTypeId),
            )
            ->with('options')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return BaseCollection<int, Collection<int, CustomField>>
     */
    public function fieldsGroupedByDeviceType(): BaseCollection
    {
        return DeviceType::query()
            ->with(['customFields.options'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(
                fn (DeviceType $type): array => [$type->id => $type->customFields],
            );
    }

    /**
     * @param  list<string>  $optionNames
     * @param  list<int>  $deviceTypeIds
     * @param  list<int>  $serviceTypeIds
     */
    public function saveDefinition(
        ?CustomField $field,
        string $name,
        array $optionNames,
        array $deviceTypeIds,
        array $serviceTypeIds,
    ): CustomField {
        return DB::transaction(function () use ($field, $name, $optionNames, $deviceTypeIds, $serviceTypeIds): CustomField {
            $field ??= new CustomField;
            $field->fill(['name' => $name]);
            $field->save();

            $uniqueNames = [];
            foreach ($optionNames as $optionName) {
                $trimmed = trim($optionName);
                if ($trimmed !== '') {
                    $uniqueNames[$trimmed] = $trimmed;
                }
            }

            $field->options()->delete();
            foreach (array_values($uniqueNames) as $optionName) {
                $field->options()->create(['name' => $optionName]);
            }

            $field->deviceTypes()->sync($deviceTypeIds);
            $field->serviceTypes()->sync($serviceTypeIds);

            return $field->load(['options', 'deviceTypes', 'serviceTypes']);
        });
    }

    /**
     * @param  array<int|string, mixed>  $valuesByFieldId
     */
    public function saveDeviceValues(Device $device, array $valuesByFieldId): void
    {
        $this->persistValues(
            ownerId: (int) $device->id,
            ownerColumn: 'device_id',
            fields: $this->fieldsForDeviceType((int) $device->device_type_id),
            valuesByFieldId: $valuesByFieldId,
            modelClass: DeviceCustomFieldValue::class,
        );
    }

    /**
     * @return array<int, string>
     */
    public function valuesForDevice(Device $device): array
    {
        return DeviceCustomFieldValue::query()
            ->where('device_id', $device->id)
            ->get()
            ->mapWithKeys(
                fn (DeviceCustomFieldValue $row): array => [$row->custom_field_id => $row->value],
            )
            ->all();
    }

    /**
     * @param  array<int|string, mixed>  $valuesByFieldId
     */
    public function saveServiceRequestValues(int $serviceRequestId, int $serviceTypeId, array $valuesByFieldId): void
    {
        $this->persistValues(
            ownerId: $serviceRequestId,
            ownerColumn: 'service_request_id',
            fields: $this->fieldsForServiceType($serviceTypeId),
            valuesByFieldId: $valuesByFieldId,
            modelClass: ServiceRequestCustomFieldValue::class,
        );
    }

    /**
     * @return array<int, string>
     */
    public function valuesForServiceRequest(int $serviceRequestId): array
    {
        return ServiceRequestCustomFieldValue::query()
            ->where('service_request_id', $serviceRequestId)
            ->get()
            ->mapWithKeys(
                fn (ServiceRequestCustomFieldValue $row): array => [$row->custom_field_id => $row->value],
            )
            ->all();
    }

    /**
     * @param  Collection<int, CustomField>  $fields
     * @param  array<int|string, mixed>  $valuesByFieldId
     * @param  class-string<DeviceCustomFieldValue|ServiceRequestCustomFieldValue>  $modelClass
     */
    private function persistValues(
        int $ownerId,
        string $ownerColumn,
        Collection $fields,
        array $valuesByFieldId,
        string $modelClass,
    ): void {
        DB::transaction(function () use ($ownerId, $ownerColumn, $fields, $valuesByFieldId, $modelClass): void {
            $allowedIds = $fields->modelKeys();

            $modelClass::query()
                ->where($ownerColumn, $ownerId)
                ->when(
                    $allowedIds !== [],
                    fn ($query) => $query->whereNotIn('custom_field_id', $allowedIds),
                )
                ->delete();

            if ($allowedIds === []) {
                return;
            }

            foreach ($fields as $field) {
                $raw = $valuesByFieldId[$field->id] ?? $valuesByFieldId[(string) $field->id] ?? '';
                $value = trim(is_string($raw) ? $raw : (string) $raw);

                if ($value === '') {
                    $modelClass::query()
                        ->where($ownerColumn, $ownerId)
                        ->where('custom_field_id', $field->id)
                        ->delete();

                    continue;
                }

                if ($field->options->isNotEmpty()) {
                    $optionNames = $field->options->pluck('name')->all();
                    if (! in_array($value, $optionNames, true)) {
                        throw new InvalidArgumentException(
                            "Invalid option for custom field [{$field->name}].",
                        );
                    }
                }

                $modelClass::query()->updateOrCreate(
                    [
                        $ownerColumn => $ownerId,
                        'custom_field_id' => $field->id,
                    ],
                    ['value' => $value],
                );
            }
        });
    }
}
