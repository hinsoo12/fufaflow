<?php

namespace App\Enums;

enum MembershipRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Manager => 'Manager',
            self::Staff => 'Staff',
        };
    }

    /**
     * The abilities granted to this role.
     *
     * @return list<Ability>
     */
    public function abilities(): array
    {
        return match ($this) {
            self::Owner => Ability::cases(),
            self::Manager => array_values(array_filter(
                Ability::cases(),
                fn (Ability $ability): bool => ! in_array($ability, [
                    Ability::TeamManage,
                    Ability::SettingsManage,
                    Ability::AuditView,
                ], true),
            )),
            self::Staff => [
                Ability::CustomersView,
                Ability::CustomersManage,
                Ability::PlansView,
                Ability::SubscriptionsView,
                Ability::SubscriptionsStart,
                Ability::BillingView,
                Ability::PaymentsRecord,
            ],
        };
    }

    public function allows(Ability $ability): bool
    {
        return in_array($ability, $this->abilities(), true);
    }

    /**
     * Roles an owner can give to invited members.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function assignable(): array
    {
        return array_map(
            fn (self $role): array => ['value' => $role->value, 'label' => $role->label()],
            [self::Manager, self::Staff],
        );
    }
}
