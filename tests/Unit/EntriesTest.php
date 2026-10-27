<?php

declare(strict_types=1);

use PhpLives\Entries;
use PhpLives\Entry;
use PhpLives\InvalidEntries;

beforeEach(function () {
    $this->dir = sys_get_temp_dir().'/phplives-entries-'.bin2hex(random_bytes(4));
    mkdir($this->dir);
    $this->today = new DateTimeImmutable('2026-10-16');
});

afterEach(function () {
    array_map(unlink(...), glob($this->dir.'/*'));
    rmdir($this->dir);
});

it('loads every entry, newest first', function () {
    writeEntry($this->dir, '2026-08-01-older.php', ['name' => 'Older']);
    writeEntry($this->dir, '2026-10-01-newer.php', ['name' => 'Newer']);
    writeEntry($this->dir, '2026-09-01-middle.php', ['name' => 'Middle']);

    $names = array_map(fn (Entry $e) => $e->name, Entries::load($this->dir, $this->today)->all());

    expect($names)->toBe(['Newer', 'Middle', 'Older']);
});

it('sorts entries with the same date by slug', function () {
    writeEntry($this->dir, '2026-09-01-beta.php');
    writeEntry($this->dir, '2026-09-01-alpha.php');

    $slugs = array_map(fn (Entry $e) => $e->slug, Entries::load($this->dir, $this->today)->all());

    expect($slugs)->toBe(['alpha', 'beta']);
});

it('ignores files that are not PHP', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    file_put_contents($this->dir.'/README.md', 'notes');

    expect(Entries::load($this->dir, $this->today)->all())->toHaveCount(1);
});

it('turns an entry file into an Entry', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['makers' => [
        ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
        ['name' => 'coolLabs'],
    ]]);

    $entry = Entries::load($this->dir, $this->today)->all()[0];

    expect($entry->slug)->toBe('pest-5')
        ->and($entry->name)->toBe('Pest 5')
        ->and($entry->shipped->format('d/m/Y'))->toBe('01/09/2026')
        ->and($entry->added->format('d/m/Y'))->toBe('16/10/2026')
        ->and($entry->stack)->toBe('php')
        ->and($entry->proofKind)->toBe('release')
        ->and($entry->makers[0]->name)->toBe('Nuno Maduro')
        ->and($entry->makers[0]->github)->toBe('nunomaduro')
        ->and($entry->makers[1]->github)->toBeNull()
        ->and($entry->topics)->toBe([]);
});

it('reads the topics of an entry', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['topics' => ['testing', 'devtools']]);

    expect(Entries::load($this->dir, $this->today)->all()[0]->topics)->toBe(['testing', 'devtools']);
});

it('fails with every problem in every file', function () {
    writeEntry($this->dir, '2026-09-01-good.php');
    writeEntry($this->dir, '2026-09-02-bad-stack.php', ['stack' => 'drupal']);
    writeEntry($this->dir, '2026-09-03-bad-link.php', ['source' => 'http://example.com']);

    try {
        Entries::load($this->dir, $this->today);
        $this->fail('Expected InvalidEntries');
    } catch (InvalidEntries $e) {
        expect($e->errors)->toHaveKeys(['2026-09-02-bad-stack.php', '2026-09-03-bad-link.php'])
            ->not->toHaveKey('2026-09-01-good.php')
            ->and($e->getMessage())->toContain('2026-09-02-bad-stack.php: Unknown stack: drupal.')
            ->and($e->getMessage())->toContain('2026-09-03-bad-link.php: Field source is not an HTTPS link.');
    }
});

it('fails on a file that does not return an array', function () {
    file_put_contents($this->dir.'/2026-09-01-broken.php', '<?php return "nope";');

    Entries::load($this->dir, $this->today);
})->throws(InvalidEntries::class, '2026-09-01-broken.php: The file must return an array.');

it('fails on two entries with the same slug', function () {
    writeEntry($this->dir, '2026-09-01-pest.php');
    writeEntry($this->dir, '2026-09-02-pest.php');

    Entries::load($this->dir, $this->today);
})->throws(InvalidEntries::class, '2026-09-02-pest.php: The slug pest is already used by 2026-09-01-pest.php.');

it('keeps entries from the last 6 months on the main page', function () {
    writeEntry($this->dir, '2026-04-16-edge.php', ['checked' => '2026-10-16']);
    writeEntry($this->dir, '2026-04-15-old.php', ['checked' => '2026-10-16']);
    writeEntry($this->dir, '2026-10-01-new.php');

    $entries = Entries::load($this->dir, $this->today);

    expect(array_map(fn (Entry $e) => $e->slug, $entries->current()))->toBe(['new', 'edge'])
        ->and(array_map(fn (Entry $e) => $e->slug, $entries->archived()))->toBe(['old']);
});

it('loads an empty folder', function () {
    expect(Entries::load($this->dir, $this->today)->all())->toBe([]);
});

it('moves the cutoff to the last day of a shorter month', function (string $today, string $cutoff) {
    expect(Entries::cutoff(new DateTimeImmutable($today))->format('Y-m-d'))->toBe($cutoff);
})->with([
    ['2026-10-16', '2026-04-16'],
    ['2026-08-31', '2026-02-28'],
    ['2028-08-31', '2028-02-29'],
    ['2026-01-15', '2025-07-15'],
]);

it('knows the day an entry moves to the archive: the first day the cutoff passes its ship date', function (string $shipped, string $archived) {
    expect(Entries::archivedOn(new DateTimeImmutable($shipped))->format('Y-m-d'))->toBe($archived);
})->with([
    ['2026-04-26', '2026-10-27'],
    ['2026-03-01', '2026-09-02'],
    ['2026-08-31', '2027-03-01'],
    ['2026-02-28', '2026-09-01'],
]);

it('agrees with the cutoff on the day an entry moves to the archive', function () {
    $shipped = new DateTimeImmutable('2026-04-26');
    $day = Entries::archivedOn($shipped);

    expect(Entries::cutoff($day) > $shipped)->toBeTrue()
        ->and(Entries::cutoff($day->modify('-1 day')) > $shipped)->toBeFalse();
});
