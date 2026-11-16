<?php

declare(strict_types=1);

use PhpLives\Http;
use Tests\FakeDevNull;

it('reads JSON and its status', function () {
    expect(Http::getJson(FakeDevNull::url().'/api/v1/users/nunomaduro'))
        ->toBe([200, ['data' => ['handle' => 'nunomaduro']]]);
});

it('reads the body of an error too', function () {
    expect(Http::getJson(FakeDevNull::url().'/broken'))->toBe([500, ['message' => 'Server Error']])
        ->and(Http::getJson(FakeDevNull::url().'/api/v1/users/nobody'))->toBe([404, ['message' => 'Not found']]);
});

it('gives no data when the body is not JSON', function () {
    expect(Http::getJson(FakeDevNull::url().'/not-json'))->toBe([200, null]);
});

it('gives status 0 when nothing answers', function () {
    expect(Http::getJson(FakeDevNull::deadUrl().'/api/v1/users/nunomaduro'))->toBe([0, null]);
});

it('follows redirects to the final answer', function () {
    expect(Http::getJson(FakeDevNull::url().'/moved'))->toBe([200, ['data' => ['handle' => 'nunomaduro']]])
        ->and(Http::status(FakeDevNull::url().'/moved'))->toBe(200);
});

it('gives the status of a link', function () {
    expect(Http::status(FakeDevNull::url().'/api/v1/users/nobody'))->toBe(404)
        ->and(Http::status(FakeDevNull::deadUrl()))->toBe(0);
});

it('sends the method it is given', function () {
    expect(Http::status(FakeDevNull::url().'/head-only', 'HEAD'))->toBe(204)
        ->and(Http::status(FakeDevNull::url().'/head-only', 'GET'))->toBe(405);
});

it('says why a request failed when nothing answers', function () {
    expect(Http::answer(FakeDevNull::deadUrl()))->toBe([0, Http::NO_ANSWER])
        ->and(Http::answer(FakeDevNull::url().'/broken'))->toBe([500, null])
        ->and(Http::answer(str_replace('http://', 'https://', FakeDevNull::url())))->toBe([0, Http::TLS_ERROR]);
});

it('tells an unknown host and a bad certificate from a server that does not answer', function (string $message, string $failure) {
    expect(Http::failure($message))->toBe($failure);
})->with([
    ['file_get_contents(https://nope.dev): Failed to open stream: php_network_getaddresses: getaddrinfo for nope.dev failed: Name or service not known', Http::UNKNOWN_HOST],
    ['file_get_contents(): php_network_getaddresses: getaddrinfo for nope.dev failed: nodename nor servname provided, or not known', Http::UNKNOWN_HOST],
    ['file_get_contents(): php_network_getaddresses: getaddrinfo for example.com failed: Temporary failure in name resolution', Http::NO_ANSWER],
    ["file_get_contents(): SSL operation failed with code 1. OpenSSL Error messages:\nerror:0A000086:SSL routines::certificate verify failed", Http::TLS_ERROR],
    ["file_get_contents(): Peer certificate subjectAltName did not match expected name `example.com'", Http::TLS_ERROR],
    ['file_get_contents(): Failed to enable crypto', Http::TLS_ERROR],
    ['file_get_contents(http://127.0.0.1:9): Failed to open stream: Connection refused', Http::NO_ANSWER],
    ['file_get_contents(http://10.255.255.1): Failed to open stream: Operation timed out', Http::NO_ANSWER],
    ['file_get_contents(https://example.com): Failed to open stream: HTTP request failed!', Http::NO_ANSWER],
]);
