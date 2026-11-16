<?php

declare(strict_types=1);

use PhpLives\LinkWarnings;
use Tests\FakeDevNull;

beforeEach(function () {
    $this->tmp = sys_get_temp_dir().'/phplives-links-'.bin2hex(random_bytes(4));
    mkdir($this->tmp.'/entries', 0777, true);
});

afterEach(function () {
    exec('rm -rf '.escapeshellarg($this->tmp));
});

/**
 * An entry whose links all point at one local base URL, so the test never
 * leaves this machine.
 */
function entryAt(string $base, string $dir, string $file, string $name): void
{
    writeEntry($dir, $file, [
        'makers' => [['name' => 'Someone']],
        'proof' => ['kind' => 'post', 'url' => "{$base}/{$name}/proof"],
        'source' => "{$base}/{$name}",
        'devnull' => "Shipped with PHP: {$name}.",
    ]);
}

/**
 * An entry whose links fail for good: they ask for HTTPS from a server that
 * only speaks HTTP, so every request ends in a TLS error.
 */
function brokenEntry(string $dir, string $file, string $name): string
{
    $base = str_replace('http://', 'https://', FakeDevNull::url());
    entryAt($base, $dir, $file, $name);

    return $base;
}

/**
 * An entry whose links point at a local port where nothing listens, as a site
 * that is down for a while would.
 */
function downEntry(string $dir, string $file, string $name): string
{
    $base = str_replace('http://', 'https://', FakeDevNull::deadUrl());
    entryAt($base, $dir, $file, $name);

    return $base;
}

function checkLinks(string $tmp, array $files = [], array $options = []): array
{
    $command = implode(' ', array_map(escapeshellarg(...), [
        PHP_BINARY, dirname(__DIR__, 2).'/bin/check-links', "--entries={$tmp}/entries", '--today=2026-10-16', '--wait=0', ...$options, ...$files,
    ]));
    exec($command.' 2>&1', $output, $status);

    return [$status, implode("\n", $output)];
}

it('checks only the entry files it is given, and fails on a broken link', function () {
    $dead = brokenEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');
    brokenEntry($this->tmp.'/entries', '2026-09-02-second.php', 'second');

    [$status, $output] = checkLinks($this->tmp, ['entries/2026-09-01-first.php']);

    expect($status)->toBe(1)
        ->and($output)->toContain('2026-09-01-first.php')
        ->and($output)->toContain("broken (TLS error) {$dead}/first/proof")
        ->and($output)->toContain("broken (TLS error) {$dead}/first")
        ->and($output)->toContain('2 links checked, 2 broken, 0 warnings.')
        ->and($output)->not->toContain('second');
});

it('checks every entry when it is given no files', function () {
    brokenEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');
    brokenEntry($this->tmp.'/entries', '2026-09-02-second.php', 'second');

    [$status, $output] = checkLinks($this->tmp);

    expect($status)->toBe(1)
        ->and($output)->toContain('2026-09-01-first.php')
        ->and($output)->toContain('2026-09-02-second.php')
        ->and($output)->toContain('4 links checked, 4 broken, 0 warnings.');
});

it('fails when a file is not an entry', function () {
    brokenEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');

    [$status, $output] = checkLinks($this->tmp, ['entries/2026-09-01-other.php']);

    expect($status)->toBe(1)
        ->and($output)->toContain('Not an entry file: 2026-09-01-other.php')
        ->and($output)->not->toContain('links checked');
});

it('fails when an entry is not valid', function () {
    writeEntry($this->tmp.'/entries', '2026-09-01-first.php', ['stack' => 'drupal']);

    [$status, $output] = checkLinks($this->tmp);

    expect($status)->toBe(1)
        ->and($output)->toContain('2026-09-01-first.php: Unknown stack: drupal.');
});

it('only warns when a site does not answer, and does not fail', function () {
    $down = downEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');

    [$status, $output] = checkLinks($this->tmp);

    expect($status)->toBe(0)
        ->and($output)->toContain("warning (no answer) {$down}/first/proof")
        ->and($output)->toContain('2 links checked, 0 broken, 2 warnings.')
        ->and($output)->not->toContain('::warning');
});

it('fails on a broken link even when other links only warn', function () {
    downEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');
    brokenEntry($this->tmp.'/entries', '2026-09-02-second.php', 'second');

    [$status, $output] = checkLinks($this->tmp);

    expect($status)->toBe(1)
        ->and($output)->toContain('4 links checked, 2 broken, 2 warnings.');
});

it('marks each warning for GitHub when asked to', function () {
    $down = downEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');

    [, $output] = checkLinks($this->tmp, options: ['--annotate']);

    expect($output)->toContain("::warning file=entries/2026-09-01-first.php::No answer from {$down}/first/proof");
});

it('fails on a link that warned on the run before too', function () {
    $down = downEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');
    $state = $this->tmp.'/warnings.json';

    [$first] = checkLinks($this->tmp, options: ["--state={$state}"]);
    [$second, $output] = checkLinks($this->tmp, options: ["--state={$state}"]);

    expect($first)->toBe(0)
        ->and($second)->toBe(1)
        ->and($output)->toContain("broken (no answer, ".LinkWarnings::RUNS_TO_FAIL." runs in a row) {$down}/first/proof")
        ->and($output)->toContain('2 links checked, 2 broken, 0 warnings.');
});

it('forgets a warning once the link works again', function () {
    $state = $this->tmp.'/warnings.json';
    file_put_contents($state, json_encode(['https://example.com/gone' => 1]));
    $down = downEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');

    checkLinks($this->tmp, options: ["--state={$state}"]);

    expect(json_decode(file_get_contents($state), true))->toBe([
        "{$down}/first/proof" => 1,
        "{$down}/first" => 1,
    ]);
});

it('starts again when the state file is missing or not valid', function (?string $contents) {
    $state = $this->tmp.'/warnings.json';

    if ($contents !== null) {
        file_put_contents($state, $contents);
    }

    downEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');

    [$status] = checkLinks($this->tmp, options: ["--state={$state}"]);

    expect($status)->toBe(0);
})->with([null, 'not json', '["a list"]']);
