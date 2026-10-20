<?php

declare(strict_types=1);

use Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature');

/**
 * A valid entry, as an entry file returns it. Pass changes to break one field.
 */
function validEntry(array $changes = []): array
{
    return array_replace([
        'name' => 'Pest 5',
        'sentence' => 'A testing framework for PHP, focused on simplicity.',
        'makers' => [
            ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
        ],
        'shipped' => '2026-09-01',
        'proof' => [
            'kind' => 'release',
            'url' => 'https://github.com/pestphp/pest/releases/tag/v5.0.0',
        ],
        'source' => 'https://github.com/pestphp/pest',
        'stack' => 'php',
        'devnull' => 'Pest 5 shipped. https://phplives.dev',
        'checked' => '2026-10-16',
    ], $changes);
}

/**
 * Writes an entry file to a folder. The shipped date comes from the file name.
 */
function writeEntry(string $dir, string $file, array $changes = []): void
{
    preg_match('/^(\d{4}-\d{2}-\d{2})/', $file, $match);
    $entry = validEntry(['shipped' => $match[1] ?? '2026-09-01'] + $changes);

    file_put_contents("{$dir}/{$file}", '<?php return '.var_export($entry, true).';');
}
