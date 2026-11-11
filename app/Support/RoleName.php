<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Role names from docs/CONTRACTS.md.
 */
enum RoleName: string
{
    case CompanyAdmin = 'Company Admin/Manager';
    case Operator = 'Operator';
    case TechnicalSupervisor = 'Technical Supervisor';
    case Accountant = 'Accountant';
    case Technician = 'Technician';
    case Customer = 'Customer';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CompanyAdmin => 'مدیر شرکت',
            self::Operator => 'اپراتور',
            self::TechnicalSupervisor => 'ناظر فنی',
            self::Accountant => 'حسابدار',
            self::Technician => 'تکنسین',
            self::Customer => 'مشتری',
        };
    }
}
