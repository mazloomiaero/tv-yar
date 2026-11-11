<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Sample status labels shown in Admin service-type mappings.
 * Architecture document lists English samples; the catalog stores Persian names for UI.
 * Codes stay stable when a display name is renamed.
 */
final class SampleServiceStatus
{
    /**
     * @return list<array{code: string, name: string, english: string}>
     */
    public static function definitions(): array
    {
        return [
            ['code' => InitialServiceStatus::CODE, 'name' => InitialServiceStatus::NAME, 'english' => 'New'],
            ['code' => 'waiting_for_contact', 'name' => 'در انتظار تماس', 'english' => 'Waiting for Contact'],
            ['code' => 'waiting_for_assignment', 'name' => 'در انتظار ارجاع', 'english' => 'Waiting for Assignment'],
            ['code' => 'assigned', 'name' => 'ارجاع‌شده', 'english' => 'Assigned'],
            ['code' => 'technician_confirmed', 'name' => 'تأیید تکنسین', 'english' => 'Technician Confirmed'],
            ['code' => 'on_the_way', 'name' => 'در مسیر', 'english' => 'On the Way'],
            ['code' => 'on_site', 'name' => 'در محل', 'english' => 'On Site'],
            ['code' => 'inspecting', 'name' => 'در حال بررسی', 'english' => 'Inspecting'],
            ['code' => 'repairing', 'name' => 'در حال تعمیر', 'english' => 'Repairing'],
            ['code' => 'repaired', 'name' => 'تعمیر شده', 'english' => 'Repaired'],
            ['code' => 'closed', 'name' => 'بسته', 'english' => 'Closed'],
            ['code' => 'cancelled', 'name' => 'لغو شده', 'english' => 'Cancelled'],
            ['code' => 'customer_absent', 'name' => 'مشتری غایب', 'english' => 'Customer Absent'],
            ['code' => 'cannot_repair', 'name' => 'غیرقابل تعمیر', 'english' => 'Cannot Repair'],
            ['code' => 'revisit_needed', 'name' => 'نیاز به بازدید مجدد', 'english' => 'Revisit Needed'],
            ['code' => 'transfer_to_workshop', 'name' => 'انتقال به کارگاه', 'english' => 'Transfer to Workshop'],
            ['code' => WorkshopServiceStatus::DeviceReceivedCode, 'name' => WorkshopServiceStatus::DeviceReceived, 'english' => 'Device Received'],
            ['code' => WorkshopServiceStatus::InWorkshopCode, 'name' => WorkshopServiceStatus::InWorkshop, 'english' => 'In Workshop'],
            ['code' => WorkshopServiceStatus::ReadyForDeliveryCode, 'name' => WorkshopServiceStatus::ReadyForDelivery, 'english' => 'Ready for Delivery'],
            ['code' => WorkshopServiceStatus::DeliveredCode, 'name' => WorkshopServiceStatus::Delivered, 'english' => 'Delivered'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_map(
            static fn (array $definition): string => $definition['name'],
            self::definitions(),
        );
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_map(
            static fn (array $definition): string => $definition['code'],
            self::definitions(),
        );
    }

    /**
     * @return array<string, string>
     */
    public static function englishToPersian(): array
    {
        $map = [];

        foreach (self::definitions() as $definition) {
            $map[$definition['english']] = $definition['name'];
        }

        return $map;
    }
}
