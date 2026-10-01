<?php

namespace App\Enums;

enum IntervalUnit: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    /**
     * Human description of a billing interval, e.g. "Monthly", "Every 3 months".
     */
    public function describe(int $count): string
    {
        if ($count === 1) {
            return match ($this) {
                self::Day => 'Daily',
                self::Week => 'Weekly',
                self::Month => 'Monthly',
                self::Year => 'Yearly',
            };
        }

        return "Every {$count} {$this->value}s";
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $unit): array => ['value' => $unit->value, 'label' => ucfirst($unit->value).'(s)'],
            self::cases(),
        );
    }
}
