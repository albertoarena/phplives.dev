<?php

declare(strict_types=1);

use PhpLives\Entry;
use PhpLives\LinkCheck;

/**
 * A fake web: answers from a map of "METHOD url" to a status, and records each
 * request. A request that is not in the map gets a 404.
 */
function fakeWeb(array $statuses, ?array &$requests = []): LinkCheck
{
    return new LinkCheck(function (string $url, string $method) use ($statuses, &$requests): int {
        $requests[] = "{$method} {$url}";

        return $statuses["{$method} {$url}"] ?? 404;
    });
}

it('lists every link in an entry, once each', function () {
    $entry = Entry::fromArray('pest-5', validEntry([
        'makers' => [
            ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
            ['name' => 'coolLabs'],
            ['name' => 'Brent Roose', 'github' => 'brendt'],
        ],
        'proof' => ['kind' => 'release', 'url' => 'https://github.com/pestphp/pest/releases/tag/v5.0.0'],
        'source' => 'https://github.com/pestphp/pest',
        'devnull' => "Shipped with PHP: Pest 5 (https://github.com/pestphp/pest).\n\nSee more at https://phplives.dev.",
    ]));

    expect(LinkCheck::links($entry))->toBe([
        'https://github.com/pestphp/pest/releases/tag/v5.0.0',
        'https://github.com/pestphp/pest',
        'https://github.com/nunomaduro',
        'https://github.com/brendt',
        'https://phplives.dev',
    ]);
});

it('checks the docs link and the Packagist page when an entry gives them', function () {
    $entry = Entry::fromArray('pest-5', validEntry([
        'devnull' => 'Shipped with PHP: Pest 5.',
        'docs' => 'https://pestphp.com/docs',
        'package' => 'pestphp/pest',
    ]));

    expect(LinkCheck::links($entry))->toBe([
        'https://github.com/pestphp/pest/releases/tag/v5.0.0',
        'https://github.com/pestphp/pest',
        'https://pestphp.com/docs',
        'https://packagist.org/packages/pestphp/pest',
        'https://github.com/nunomaduro',
    ]);
});

it('accepts a link that answers with a success', function (int $status) {
    expect(fakeWeb(['HEAD https://example.com' => $status])->status('https://example.com'))->toBe($status);
})->with([200, 204]);

it('asks for the whole page when a server refuses a HEAD request', function () {
    $check = fakeWeb([
        'HEAD https://example.com' => 405,
        'GET https://example.com' => 200,
    ], $requests);

    expect($check->status('https://example.com'))->toBe(200)
        ->and($requests)->toBe(['HEAD https://example.com', 'GET https://example.com']);
});

it('does not ask twice when HEAD works', function () {
    fakeWeb(['HEAD https://example.com' => 200], $requests)->status('https://example.com');

    expect($requests)->toBe(['HEAD https://example.com']);
});

it('gives the status of the whole page when both fail', function () {
    expect(fakeWeb(['HEAD https://example.com' => 403, 'GET https://example.com' => 410])->status('https://example.com'))
        ->toBe(410);
});

it('says which statuses are good', function (int $status, bool $ok) {
    expect(LinkCheck::ok($status))->toBe($ok);
})->with([
    [200, true],
    [299, true],
    [0, false],
    [199, false],
    [301, false],
    [404, false],
    [429, false],
    [500, false],
]);
