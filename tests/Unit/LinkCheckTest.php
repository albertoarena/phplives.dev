<?php

declare(strict_types=1);

use PhpLives\Entry;
use PhpLives\Http;
use PhpLives\LinkCheck;
use PhpLives\LinkStatus;

/**
 * A fake web: answers from a map of "METHOD url" to a status, and records each
 * request. A request that is not in the map gets a 404. A status of 0 is a
 * server that gives no answer, and a string is a request that failed for that
 * reason.
 */
function fakeWeb(array $statuses, ?array &$requests = []): LinkCheck
{
    return new LinkCheck(function (string $url, string $method) use (&$statuses, &$requests): array {
        $requests[] = "{$method} {$url}";
        $status = $statuses["{$method} {$url}"] ?? 404;

        // A list of statuses gives one answer per request, in order.
        $status = is_array($status) ? array_shift($statuses["{$method} {$url}"]) : $status;

        return match (true) {
            is_string($status) => [0, $status],
            $status === 0 => [0, Http::NO_ANSWER],
            default => [$status, null],
        };
    }, function (int $seconds) use (&$requests): void {
        $requests[] = "wait {$seconds}";
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
    expect(fakeWeb(['HEAD https://example.com' => $status])->status('https://example.com')->code)->toBe($status);
})->with([200, 204]);

it('asks for the whole page when a server refuses a HEAD request', function () {
    $check = fakeWeb([
        'HEAD https://example.com' => 405,
        'GET https://example.com' => 200,
    ], $requests);

    expect($check->status('https://example.com')->code)->toBe(200)
        ->and($requests)->toBe(['HEAD https://example.com', 'GET https://example.com']);
});

it('does not ask twice when HEAD works', function () {
    fakeWeb(['HEAD https://example.com' => 200], $requests)->status('https://example.com');

    expect($requests)->toBe(['HEAD https://example.com']);
});

it('gives the status of the whole page when both fail', function () {
    expect(fakeWeb(['HEAD https://example.com' => 403, 'GET https://example.com' => 410])->status('https://example.com')->code)
        ->toBe(410);
});

it('waits and tries once more when a link gives no answer', function () {
    $check = fakeWeb([
        'HEAD https://example.com' => [0, 200],
        'GET https://example.com' => 0,
    ], $requests);

    expect($check->status('https://example.com')->code)->toBe(200)
        ->and($requests)->toBe([
            'HEAD https://example.com',
            'GET https://example.com',
            'wait '.LinkCheck::RETRY_SECONDS,
            'HEAD https://example.com',
        ]);
});

it('gives no answer when the second try gets none either', function () {
    $check = fakeWeb(['HEAD https://example.com' => 0, 'GET https://example.com' => 0], $requests);

    expect($check->status('https://example.com')->label())->toBe('no answer')
        ->and($requests)->toHaveCount(5);
});

it('does not try again when a link answers with an error', function () {
    fakeWeb(['HEAD https://example.com' => 404, 'GET https://example.com' => 404], $requests)
        ->status('https://example.com');

    expect($requests)->toBe(['HEAD https://example.com', 'GET https://example.com']);
});

it('waits and tries once more when a server fails or is too busy', function (int $status) {
    $check = fakeWeb([
        'HEAD https://example.com' => [$status, 200],
        'GET https://example.com' => $status,
    ], $requests);

    expect($check->status('https://example.com')->code)->toBe(200)
        ->and($requests)->toBe([
            'HEAD https://example.com',
            'GET https://example.com',
            'wait '.LinkCheck::RETRY_SECONDS,
            'HEAD https://example.com',
        ]);
})->with([500, 503, 429]);

it('does not try again when the host is unknown or its certificate is bad', function (string $failure) {
    $status = fakeWeb(['HEAD https://example.com' => $failure, 'GET https://example.com' => $failure], $requests)
        ->status('https://example.com');

    expect($status->label())->toBe($failure)
        ->and($status->fails())->toBeTrue()
        ->and($requests)->toBe(['HEAD https://example.com', 'GET https://example.com']);
})->with([Http::UNKNOWN_HOST, Http::TLS_ERROR]);

it('says which answers are good, which only warn and which fail', function (LinkStatus $status, string $verdict) {
    expect([$status->ok(), $status->warns(), $status->fails()])->toBe([
        $verdict === 'ok',
        $verdict === 'warn',
        $verdict === 'fail',
    ]);
})->with([
    [new LinkStatus(200), 'ok'],
    [new LinkStatus(299), 'ok'],
    [new LinkStatus(500), 'warn'],
    [new LinkStatus(503), 'warn'],
    [new LinkStatus(429), 'warn'],
    [new LinkStatus(0, Http::NO_ANSWER), 'warn'],
    [new LinkStatus(199), 'fail'],
    [new LinkStatus(301), 'fail'],
    [new LinkStatus(401), 'fail'],
    [new LinkStatus(403), 'fail'],
    [new LinkStatus(404), 'fail'],
    [new LinkStatus(410), 'fail'],
    [new LinkStatus(0, Http::UNKNOWN_HOST), 'fail'],
    [new LinkStatus(0, Http::TLS_ERROR), 'fail'],
]);

it('labels an answer with its status, or with why it failed', function () {
    expect((new LinkStatus(500))->label())->toBe('500')
        ->and((new LinkStatus(0, Http::UNKNOWN_HOST))->label())->toBe('unknown host');
});
