<?php

namespace App\Billing;

use App\Enums\IntervalUnit;
use Carbon\CarbonImmutable;

/**
 * Pure calendar arithmetic for subscription periods. No database access.
 *
 * Periods are inclusive [starts_on, ends_on]; the next period starts the day
 * after ends_on. Month and year intervals keep an "anchor day" so a
 * subscription started on the 31st clamps to the end of short months and
 * returns to the 31st afterwards:
 *
 *   31 Jan → next start 28/29 Feb → next start 31 Mar → next start 30 Apr
 */
final class PeriodCalculator
{
    public function nextStart(CarbonImmutable $start, IntervalUnit $unit, int $count, int $anchorDay): CarbonImmutable
    {
        return match ($unit) {
            IntervalUnit::Day => $start->addDays($count),
            IntervalUnit::Week => $start->addDays(7 * $count),
            IntervalUnit::Month => $this->addAnchoredMonths($start, $count, $anchorDay),
            IntervalUnit::Year => $this->addAnchoredMonths($start, 12 * $count, $anchorDay),
        };
    }

    /**
     * The inclusive last day of the period that starts on $start.
     */
    public function endOf(CarbonImmutable $start, IntervalUnit $unit, int $count, int $anchorDay): CarbonImmutable
    {
        return $this->nextStart($start, $unit, $count, $anchorDay)->subDay();
    }

    /**
     * Consecutive periods starting at $firstStart.
     *
     * @return list<array{starts_on: CarbonImmutable, ends_on: CarbonImmutable}>
     */
    public function periods(CarbonImmutable $firstStart, IntervalUnit $unit, int $count, int $anchorDay, int $numberOfPeriods): array
    {
        $periods = [];
        $start = $firstStart;

        for ($i = 0; $i < $numberOfPeriods; $i++) {
            $next = $this->nextStart($start, $unit, $count, $anchorDay);
            $periods[] = ['starts_on' => $start, 'ends_on' => $next->subDay()];
            $start = $next;
        }

        return $periods;
    }

    private function addAnchoredMonths(CarbonImmutable $start, int $months, int $anchorDay): CarbonImmutable
    {
        $targetMonth = $start->startOfMonth()->addMonthsNoOverflow($months);
        $day = min($anchorDay, $targetMonth->daysInMonth);

        return $targetMonth->setDate($targetMonth->year, $targetMonth->month, $day)->startOfDay();
    }
}
