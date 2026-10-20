<?php

declare(strict_types=1);

use PhpLives\Bundle;
use PhpLives\Bundles;
use PhpLives\Entry;

/**
 * An entry with only the fields that the grouping reads.
 */
function parcel(string $slug, string $shipped, string $added = '2026-10-01'): Entry
{
    return Entry::fromArray($slug, validEntry(['shipped' => $shipped, 'added' => $added]));
}

function slugs(array $entries): array
{
    return array_map(fn (Entry $e) => $e->slug, $entries);
}

function heads(array $bundles): array
{
    return array_map(fn (Bundle $b) => [$b->title, slugs($b->entries)], $bundles);
}

it('starts the week on Monday, in UTC', function (string $today, string $monday) {
    expect(Bundles::weekStart(new DateTimeImmutable($today, new DateTimeZone('UTC')))->format('Y-m-d'))->toBe($monday);
})->with([
    'a Tuesday' => ['2026-10-20', '2026-10-19'],
    'the Monday itself' => ['2026-10-19', '2026-10-19'],
    'a Sunday' => ['2026-10-25', '2026-10-19'],
    'late on a Sunday' => ['2026-10-25 23:59:59', '2026-10-19'],
    'a week across two years' => ['2027-01-01', '2026-12-28'],
]);

it('puts the entries added this week on full labels, newest added first', function () {
    $entries = [
        parcel('old', '2026-10-01', '2026-10-01'),
        parcel('monday', '2026-09-01', '2026-10-19'),
        parcel('tuesday', '2026-08-01', '2026-10-20'),
        parcel('sunday-before', '2026-10-10', '2026-10-18'),
    ];

    $featured = Bundles::featured($entries, new DateTimeImmutable('2026-10-20'));

    expect(slugs($featured))->toBe(['tuesday', 'monday', 'sunday-before']);
});

it('fills the full labels with the entries added last, when fewer were added this week', function () {
    $entries = [
        parcel('a', '2026-10-01', '2026-10-05'),
        parcel('b', '2026-09-01', '2026-10-12'),
        parcel('c', '2026-08-01', '2026-09-01'),
        parcel('d', '2026-07-01', '2026-10-13'),
    ];

    expect(slugs(Bundles::featured($entries, new DateTimeImmutable('2026-10-20'))))->toBe(['d', 'b', 'a']);
});

it('keeps 3 full labels when more were added this week, and breaks ties by shipped date', function () {
    $entries = [
        parcel('one', '2026-09-01', '2026-10-20'),
        parcel('two', '2026-09-03', '2026-10-20'),
        parcel('three', '2026-09-02', '2026-10-20'),
        parcel('four', '2026-08-01', '2026-10-20'),
    ];

    expect(slugs(Bundles::featured($entries, new DateTimeImmutable('2026-10-20'))))->toBe(['two', 'three', 'one']);
});

it('counts the entries added this week', function () {
    $entries = [parcel('a', '2026-09-01', '2026-10-19'), parcel('b', '2026-09-01', '2026-10-18')];

    expect(Bundles::addedThisWeek($entries, new DateTimeImmutable('2026-10-20')))->toBe(1);
});

it('bundles the rest by shipped date: this week, the earlier weeks of this month, then months', function () {
    $entries = [
        parcel('this-week', '2026-10-19'),
        parcel('week-42', '2026-10-15'),
        parcel('week-42-too', '2026-10-12'),
        parcel('week-40', '2026-10-02'),
        parcel('september', '2026-09-30'),
        parcel('september-too', '2026-09-01'),
        parcel('july', '2026-07-20'),
    ];

    $bundles = Bundles::group($entries, new DateTimeImmutable('2026-10-20'));

    expect(heads($bundles))->toBe([
        ['Week 43, 19/10 to 25/10', ['this-week']],
        ['Week 42, 12/10 to 18/10', ['week-42', 'week-42-too']],
        ['Week 40, 01/10 to 04/10', ['week-40']],
        ['September 2026', ['september', 'september-too']],
        ['July 2026', ['july']],
    ])->and(array_map(fn (Bundle $b) => $b->kind, $bundles))->toBe(['week', 'week', 'week', 'month', 'month'])
        ->and(array_map(fn (Bundle $b) => $b->id, $bundles))->toBe(['week-2026-43', 'week-2026-42', 'week-2026-40', 'month-2026-09', 'month-2026-07']);
});

it('keeps the whole current week together when it starts in the month before', function () {
    $entries = [
        parcel('october', '2026-10-01'),
        parcel('monday', '2026-09-28'),
        parcel('september', '2026-09-27'),
    ];

    expect(heads(Bundles::group($entries, new DateTimeImmutable('2026-10-01'))))->toBe([
        ['Week 40, 28/09 to 04/10', ['october', 'monday']],
        ['September 2026', ['september']],
    ]);
});

it('uses the ISO week number across the new year', function () {
    $entries = [
        parcel('new-year', '2027-01-02'),
        parcel('december', '2026-12-31'),
    ];

    expect(heads(Bundles::group($entries, new DateTimeImmutable('2027-01-05'))))->toBe([
        ['Week 53, 01/01 to 03/01', ['new-year']],
        ['December 2026', ['december']],
    ]);
});

it('starts a new month bundle on the first day of the month', function () {
    $entries = [parcel('first', '2026-09-01'), parcel('last', '2026-08-31')];

    expect(heads(Bundles::group($entries, new DateTimeImmutable('2026-10-20'))))->toBe([
        ['September 2026', ['first']],
        ['August 2026', ['last']],
    ]);
});

it('makes no bundle for a week or a month without entries', function () {
    expect(Bundles::group([], new DateTimeImmutable('2026-10-20')))->toBe([]);
});

it('bundles the archive by month only', function () {
    $entries = [parcel('march', '2026-03-31'), parcel('march-too', '2026-03-01'), parcel('january', '2026-01-15')];

    expect(heads(Bundles::months($entries)))->toBe([
        ['March 2026', ['march', 'march-too']],
        ['January 2026', ['january']],
    ]);
});
