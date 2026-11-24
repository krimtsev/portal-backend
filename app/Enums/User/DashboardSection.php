<?php

declare(strict_types=1);

namespace App\Enums\User;

/**
 * Чтобы добавить новый раздел — добавить case и ветку в roles().
 * Чтобы изменить доступ — править только roles().
 */
enum DashboardSection: string
{
    case Panel = 'panel';
    case Users = 'users';
    case Partners = 'partners';
    case PartnerGroups = 'partner_groups';
    case PartnerNotifications = 'partner_notifications';
    case PartnerMessages = 'partner_messages';
    case Tickets = 'tickets';
    case EventCalendar = 'event_calendar';
    case Royalty = 'royalty';
    case Statistics = 'statistics';
    case Telnums = 'telnums';
    case Cloud = 'cloud';
    case PromoDialogs = 'promo_dialogs';
    case Promotions = 'promotions';
    case BankDetails = 'bank_details';
    case Settings = 'settings';

    /**
     * Какие роли имеют доступ к разделу.
     *
     * @return list<UserRole>
     */
    public function roles(): array
    {
        return match ($this) {
            self::Panel,
            self::Tickets,
            self::Statistics,
            self::Cloud => [
                UserRole::Sysadmin,
                UserRole::Admin,
                UserRole::OfficeManager,
                UserRole::Marketing,
                UserRole::Support,
            ],

            self::Users,
            self::Partners,
            self::PartnerGroups,
            self::PromoDialogs,
            self::BankDetails => [
                UserRole::Sysadmin,
                UserRole::Admin,
            ],

            self::PartnerNotifications,
            self::PartnerMessages,
            self::Settings => [UserRole::Sysadmin],

            self::EventCalendar => [
                UserRole::Sysadmin,
                UserRole::Admin,
                UserRole::OfficeManager,
                UserRole::Marketing,
            ],

            self::Royalty => [
                UserRole::Sysadmin,
                UserRole::Admin,
                UserRole::OfficeManager,
            ],

            self::Telnums => [
                UserRole::Sysadmin,
                UserRole::Admin,
                UserRole::Support,
            ],

            self::Promotions => [
                UserRole::Sysadmin,
                UserRole::Admin,
                UserRole::Marketing,
            ],
        };
    }

    public function allows(UserRole|string|null $role): bool
    {
        if ($role === null) {
            return false;
        }

        $roleEnum = $role instanceof UserRole
            ? $role
            : UserRole::tryFrom($role);

        return $roleEnum !== null && in_array($roleEnum, $this->roles(), true);
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }

    /**
     * Какие разделы доступны конкретной роли.
     *
     * @return list<string>
     */
    public static function accessibleFor(UserRole|string|null $role): array
    {
        if ($role === null) {
            return [];
        }

        $roleEnum = $role instanceof UserRole
            ? $role
            : UserRole::tryFrom($role);

        if ($roleEnum === null) {
            return [];
        }

        return array_values(array_map(
            fn (self $s) => $s->value,
            array_filter(self::cases(), fn (self $s) => $s->allows($roleEnum)),
        ));
    }
}
