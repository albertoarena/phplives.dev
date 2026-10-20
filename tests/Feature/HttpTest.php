<?php

declare(strict_types=1);

use PhpLives\Http;
use Tests\FakeDevNull;

it('reads JSON and its status', function () {
    expect(Http::getJson(FakeDevNull::url().'/api/v1/users/nunomaduro'))
        ->toBe([200, ['data' => ['username' => 'nunomaduro']]]);
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
    expect(Http::getJson(FakeDevNull::url().'/moved'))->toBe([200, ['data' => ['username' => 'nunomaduro']]])
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
