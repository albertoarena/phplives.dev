<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Splits the main page into its parts. The entries added last get a full label.
 * Every other entry is bundled by the date it shipped: this week, then each earlier
 * week of this month, then one bundle per month. Weeks are ISO weeks in UTC, from
 * Monday to Sunday.
 */
final class Bundles
{
    /** The number of full labels at the top of the main page. */
    public const int FULL_LABELS = 3;

    /** The Monday that starts the week of a day. */
    public static function weekStart(DateTimeImmutable $day): DateTimeImmutable
    {
        $day = self::utcDay($day);

        return $day->modify('-'.((int) $day->format('N') - 1).' days');
    }

    /**
     * The entries that get a full label: the entries added last, newest added first.
     * So the entries added this week come first, and when there are fewer of them
     * than FULL_LABELS, older entries fill the rest. Entries added on the same day
     * are ordered by shipped date, newest first.
     *
     * @param  list<Entry>  $entries
     * @return list<Entry>
     */
    public static function featured(array $entries, DateTimeImmutable $today, int $count = self::FULL_LABELS): array
    {
        usort($entries, fn (Entry $a, Entry $b) => [self::key($b->added), self::key($b->shipped), $a->slug]
            <=> [self::key($a->added), self::key($a->shipped), $b->slug]);

        return array_slice($entries, 0, $count);
    }

    /**
     * @param  list<Entry>  $entries
     */
    public static function addedThisWeek(array $entries, DateTimeImmutable $today): int
    {
        $monday = self::key(self::weekStart($today));

        return count(array_filter($entries, fn (Entry $e) => self::key($e->added) >= $monday));
    }

    /**
     * Bundles entries by the date they shipped. An earlier week of this month starts
     * on its Monday, or on the first of the month when that Monday is in the month
     * before. The current week is always whole.
     *
     * @param  list<Entry>  $entries  Newest first.
     * @return list<Bundle> Newest first. A week or a month without entries has no bundle.
     */
    public static function group(array $entries, DateTimeImmutable $today): array
    {
        $monday = self::weekStart($today);
        $firstOfMonth = self::utcDay($today)->modify('first day of this month');
        $weeksFrom = self::key(min($monday, $firstOfMonth));

        $bundles = [];

        foreach ($entries as $entry) {
            $shipped = self::utcDay($entry->shipped);

            if (self::key($shipped) >= $weeksFrom) {
                $start = self::weekStart($shipped);
                $from = $start < $monday && $start < $firstOfMonth ? $firstOfMonth : $start;
                $id = 'week-'.$shipped->format('o-W');
                $title = 'Week '.(int) $shipped->format('W').', '.$from->format('d/m').' to '.$start->modify('+6 days')->format('d/m');
                $bundles[$id] ??= ['week', $title, []];
            } else {
                $id = 'month-'.$shipped->format('Y-m');
                $bundles[$id] ??= ['month', $shipped->format('F Y'), []];
            }

            $bundles[$id][2][] = $entry;
        }

        return self::bundles($bundles);
    }

    /**
     * Bundles entries by the month they shipped. The archive uses it.
     *
     * @param  list<Entry>  $entries  Newest first.
     * @return list<Bundle>
     */
    public static function months(array $entries): array
    {
        $bundles = [];

        foreach ($entries as $entry) {
            $id = 'month-'.$entry->shipped->format('Y-m');
            $bundles[$id] ??= ['month', $entry->shipped->format('F Y'), []];
            $bundles[$id][2][] = $entry;
        }

        return self::bundles($bundles);
    }

    /**
     * @param  array<string, array{0: 'week'|'month', 1: string, 2: list<Entry>}>  $bundles
     * @return list<Bundle>
     */
    private static function bundles(array $bundles): array
    {
        $list = [];

        foreach ($bundles as $id => [$kind, $title, $entries]) {
            $list[] = new Bundle($id, $kind, $title, $entries);
        }

        return $list;
    }

    private static function utcDay(DateTimeImmutable $day): DateTimeImmutable
    {
        return new DateTimeImmutable($day->format('Y-m-d'), new DateTimeZone('UTC'));
    }

    private static function key(DateTimeImmutable $day): string
    {
        return $day->format('Y-m-d');
    }
}
