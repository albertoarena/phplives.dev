<?php

declare(strict_types=1);

/**
 * wrangler.jsonc, read as JSON once its comment lines are gone.
 */
function wranglerConfig(): array
{
    $lines = file(dirname(__DIR__, 2).'/wrangler.jsonc', FILE_IGNORE_NEW_LINES);
    $json = implode("\n", array_filter($lines, fn (string $line) => ! str_starts_with(ltrim($line), '//')));

    return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
}

it('deploys the built site', function () {
    expect(wranglerConfig()['assets']['directory'])->toBe('./dist');
});

it('answers a missing address with the 404 page', function () {
    expect(wranglerConfig()['assets']['not_found_handling'])->toBe('404-page');
});

it('serves the site on phplives.dev and www.phplives.dev', function () {
    expect(wranglerConfig()['routes'])->toBe([
        ['pattern' => 'phplives.dev', 'custom_domain' => true],
        ['pattern' => 'www.phplives.dev', 'custom_domain' => true],
    ]);
});
