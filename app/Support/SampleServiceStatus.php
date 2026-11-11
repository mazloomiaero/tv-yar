<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Sample status labels shown in Admin service-type mappings.
 * Architecture document lists English samples; the catalog stores Persian names for UI.
 */
final class SampleServiceStatus
{
    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return [
            'جدید',
            'در انتظار تماس',
            'در انتظار ارجاع',
            'ارجاع‌شده',
            'تأیید تکنسین',
            'در مسیر',
            'در محل',
            'در حال بررسی',
            'در حال تعمیر',
            'تعمیر شده',
            'بسته',
            'لغو شده',
            'مشتری غایب',
            'غیرقابل تعمیر',
            'نیاز به بازدید مجدد',
            'انتقال به کارگاه',
            WorkshopServiceStatus::DeviceReceived,
            WorkshopServiceStatus::InWorkshop,
            WorkshopServiceStatus::ReadyForDelivery,
            WorkshopServiceStatus::Delivered,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function englishToPersian(): array
    {
        return [
            'New' => 'جدید',
            'Waiting for Contact' => 'در انتظار تماس',
            'Waiting for Assignment' => 'در انتظار ارجاع',
            'Assigned' => 'ارجاع‌شده',
            'Technician Confirmed' => 'تأیید تکنسین',
            'On the Way' => 'در مسیر',
            'On Site' => 'در محل',
            'Inspecting' => 'در حال بررسی',
            'Repairing' => 'در حال تعمیر',
            'Repaired' => 'تعمیر شده',
            'Closed' => 'بسته',
            'Cancelled' => 'لغو شده',
            'Customer Absent' => 'مشتری غایب',
            'Cannot Repair' => 'غیرقابل تعمیر',
            'Revisit Needed' => 'نیاز به بازدید مجدد',
            'Transfer to Workshop' => 'انتقال به کارگاه',
            'Device Received' => WorkshopServiceStatus::DeviceReceived,
            'In Workshop' => WorkshopServiceStatus::InWorkshop,
            'Ready for Delivery' => WorkshopServiceStatus::ReadyForDelivery,
            'Delivered' => WorkshopServiceStatus::Delivered,
        ];
    }
}
