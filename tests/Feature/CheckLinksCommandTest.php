<?php

declare(strict_types=1);

use Tests\FakeDevNull;

beforeEach(function () {
    $this->tmp = sys_get_temp_dir().'/phplives-links-'.bin2hex(random_bytes(4));
    mkdir($this->tmp.'/entries', 0777, true);
});

afterEach(function () {
    exec('rm -rf '.escapeshellarg($this->tmp));
});

/**
 * An entry whose links all point at a local port where nothing listens, so each
 * check fails at once and the test never leaves this machine.
 */
function brokenEntry(string $dir, string $file, string $name): string
{
    $dead = str_replace('http://', 'https://', FakeDevNull::deadUrl());

    writeEntry($dir, $file, [
        'makers' => [['name' => 'Someone']],
        'proof' => ['kind' => 'post', 'url' => "{$dead}/{$name}/proof"],
        'source' => "{$dead}/{$name}",
        'devnull' => "Shipped with PHP: {$name}.",
    ]);

    return $dead;
}

function checkLinks(string $tmp, array $files = []): array
{
    $command = implode(' ', array_map(escapeshellarg(...), [
        PHP_BINARY, dirname(__DIR__, 2).'/bin/check-links', "--entries={$tmp}/entries", '--today=2026-10-16', ...$files,
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
        ->and($output)->toContain("broken (no answer) {$dead}/first/proof")
        ->and($output)->toContain("broken (no answer) {$dead}/first")
        ->and($output)->toContain('2 links checked, 2 broken.')
        ->and($output)->not->toContain('second');
});

it('checks every entry when it is given no files', function () {
    brokenEntry($this->tmp.'/entries', '2026-09-01-first.php', 'first');
    brokenEntry($this->tmp.'/entries', '2026-09-02-second.php', 'second');

    [$status, $output] = checkLinks($this->tmp);

    expect($status)->toBe(1)
        ->and($output)->toContain('2026-09-01-first.php')
        ->and($output)->toContain('2026-09-02-second.php')
        ->and($output)->toContain('4 links checked, 4 broken.');
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
