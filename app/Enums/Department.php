<?php
namespace App\Enums;

enum Department: string
{
    case Franchise = 'franchise';
    case Build = 'build';
    case Marketing = 'marketing';
    case NetworkAdmin = 'network_admin';
    case NetworkBarbering = 'network_barbering';
    case Community = 'community';
    case OfficeManager = 'office_manager';
    case ItDepartment = 'it_department';
    case Accounting = 'accounting';

    /**
     * Сопоставление slug с ID для совместимости
     */
    public function id(): int
    {
        return match($this) {
            self::Franchise => 1,
            self::Build => 2,
            self::Marketing => 3,
            self::NetworkAdmin => 4,
            self::NetworkBarbering => 5,
            self::Community => 6,
            self::OfficeManager => 7,
            self::ItDepartment => 8,
            self::Accounting => 9,
        };
    }

    /**
     * Поиск по ID
     */
    public static function fromId(int $id): ?self
    {
        return match($id) {
            1 => self::Franchise,
            2 => self::Build,
            3 => self::Marketing,
            4 => self::NetworkAdmin,
            5 => self::NetworkBarbering,
            6 => self::Community,
            7 => self::OfficeManager,
            8 => self::ItDepartment,
            9 => self::Accounting,
            default => null,
        };
    }
}
