<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Six panels from the architecture document.
 */
enum Panel: string
{
    case Admin = 'admin';
    case Operator = 'operator';
    case Technical = 'technical';
    case Accountant = 'accountant';
    case Technician = 'technician';
    case Customer = 'customer';

    public function guard(): string
    {
        return $this === self::Customer ? 'customer' : 'web';
    }

    public function role(): ?RoleName
    {
        return match ($this) {
            self::Admin => RoleName::CompanyAdmin,
            self::Operator => RoleName::Operator,
            self::Technical => RoleName::TechnicalSupervisor,
            self::Accountant => RoleName::Accountant,
            self::Technician => RoleName::Technician,
            self::Customer => null,
        };
    }

    /**
     * @return list<self>
     */
    public static function staff(): array
    {
        return [
            self::Admin,
            self::Operator,
            self::Technical,
            self::Accountant,
            self::Technician,
        ];
    }
}
