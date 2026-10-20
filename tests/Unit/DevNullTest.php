<?php

declare(strict_types=1);

use PhpLives\DevNull;
use PhpLives\Post;

/**
 * A fake API: answers from a map of URL to [status, data], and records each request.
 * A URL that is not in the map gets a 404.
 */
function fakeDevNull(array $responses, ?array &$requests = []): DevNull
{
    return new DevNull('https://dev-null.test', function (string $url) use ($responses, &$requests): array {
        $requests[] = $url;

        return $responses[$url] ?? [404, ['message' => 'Not found']];
    });
}

function user(string $username): array
{
    return [200, ['data' => ['username' => $username]]];
}

function commit(string $id, string $body, string $created, ?string $parent = null): array
{
    return [
        'id' => $id,
        'body' => $body,
        'parent_id' => $parent,
        'html_url' => "https://dev-null.test/@albertoarena/commits/{$id}",
        'created_at' => $created,
    ];
}

function commits(array $commits, ?string $nextCursor = null): array
{
    return [200, ['data' => $commits, 'meta' => ['next_cursor' => $nextCursor]]];
}

it('finds which usernames have a profile', function () {
    $devnull = fakeDevNull([
        'https://dev-null.test/api/v1/users/nunomaduro' => user('nunomaduro'),
        'https://dev-null.test/api/v1/users/brendt' => user('brendt'),
    ]);

    expect($devnull->profiles(['nunomaduro', 'someone', 'brendt']))->toBe(['nunomaduro', 'brendt']);
});

it('looks up each username once, without caring about case', function () {
    $devnull = fakeDevNull(['https://dev-null.test/api/v1/users/NativePHP' => user('NativePHP')], $requests);

    expect($devnull->profiles(['NativePHP', 'nativephp']))->toBe(['NativePHP'])
        ->and($requests)->toBe(['https://dev-null.test/api/v1/users/NativePHP']);
});

it('asks for a username as it is written, because the API cares about case', function () {
    $devnull = fakeDevNull(['https://dev-null.test/api/v1/users/xHeaven' => user('xHeaven')], $requests);

    expect($devnull->profiles(['xHeaven']))->toBe(['xHeaven'])
        ->and($requests)->toBe(['https://dev-null.test/api/v1/users/xHeaven']);
});

it('encodes the username in the URL', function () {
    fakeDevNull([], $requests)->profiles(['a/b']);

    expect($requests)->toBe(['https://dev-null.test/api/v1/users/a%2Fb']);
});

it('gives up on profiles when the API is down', function (int $status) {
    $devnull = fakeDevNull([
        'https://dev-null.test/api/v1/users/nunomaduro' => user('nunomaduro'),
        'https://dev-null.test/api/v1/users/brendt' => [$status, null],
    ], $requests);

    expect($devnull->profiles(['nunomaduro', 'brendt', 'mnapoli']))->toBeNull()
        ->and($requests)->toHaveCount(2);
})->with([
    'no answer' => 0,
    'server error' => 500,
    'rate limit' => 429,
]);

it('gives up on profiles when the API answers with something that is not a user', function () {
    $devnull = fakeDevNull(['https://dev-null.test/api/v1/users/nunomaduro' => [200, ['oops']]]);

    expect($devnull->profiles(['nunomaduro']))->toBeNull();
});

it('keeps only the posts that link to phplives.dev, newest first', function () {
    $devnull = fakeDevNull([
        'https://dev-null.test/api/v1/users/albertoarena/commits' => commits([
            commit('c3', 'Shipped with PHP: Pest 5. See more at https://phplives.dev', '2026-10-17T09:00:00Z'),
            commit('c2', 'Hello from Sicily', '2026-10-16T09:00:00Z'),
            commit('c1', 'New on PHPLives.dev: Symfony 8.1', '2026-10-15T09:00:00Z'),
        ]),
    ]);

    $posts = $devnull->posts('albertoarena');

    expect(array_map(fn (Post $p) => $p->url, $posts))->toBe([
        'https://dev-null.test/@albertoarena/commits/c3',
        'https://dev-null.test/@albertoarena/commits/c1',
    ])
        ->and($posts[0]->body)->toBe('Shipped with PHP: Pest 5. See more at https://phplives.dev')
        ->and($posts[0]->posted)->toEqual(new DateTimeImmutable('2026-10-17T09:00:00Z'));
});

it('leaves out replies', function () {
    $devnull = fakeDevNull([
        'https://dev-null.test/api/v1/users/albertoarena/commits' => commits([
            commit('c2', 'Thanks! It is on https://phplives.dev', '2026-10-17T09:00:00Z', parent: 'c0'),
            commit('c1', 'See https://phplives.dev', '2026-10-16T09:00:00Z'),
        ]),
    ]);

    expect(array_map(fn (Post $p) => $p->url, $devnull->posts('albertoarena')))
        ->toBe(['https://dev-null.test/@albertoarena/commits/c1']);
});

it('reads more pages until it has enough posts', function () {
    $devnull = fakeDevNull([
        'https://dev-null.test/api/v1/users/albertoarena/commits' => commits([
            commit('c5', 'https://phplives.dev 5', '2026-10-17T05:00:00Z'),
            commit('c4', 'Something else', '2026-10-17T04:00:00Z'),
        ], nextCursor: 'abc='),
        'https://dev-null.test/api/v1/users/albertoarena/commits?cursor=abc%3D' => commits([
            commit('c3', 'https://phplives.dev 3', '2026-10-17T03:00:00Z'),
            commit('c2', 'https://phplives.dev 2', '2026-10-17T02:00:00Z'),
        ], nextCursor: 'def'),
    ], $requests);

    expect(array_map(fn (Post $p) => $p->body, $devnull->posts('albertoarena', limit: 3)))
        ->toBe(['https://phplives.dev 5', 'https://phplives.dev 3', 'https://phplives.dev 2'])
        ->and($requests)->toHaveCount(2);
});

it('stops after a few pages', function () {
    $responses = [];

    foreach (range(0, 9) as $page) {
        $url = 'https://dev-null.test/api/v1/users/albertoarena/commits'.($page ? "?cursor=p{$page}" : '');
        $responses[$url] = commits([commit("c{$page}", 'Nothing here', '2026-10-17T00:00:00Z')], nextCursor: 'p'.($page + 1));
    }

    fakeDevNull($responses, $requests)->posts('albertoarena');

    expect($requests)->toHaveCount(DevNull::MAX_PAGES);
});

it('has no posts when the API is down', function (int $status) {
    $devnull = fakeDevNull(['https://dev-null.test/api/v1/users/albertoarena/commits' => [$status, null]]);

    expect($devnull->posts('albertoarena'))->toBeNull();
})->with([
    'no answer' => 0,
    'server error' => 500,
    'no such user' => 404,
]);

it('has no posts when the API answers with something it cannot read', function (mixed $data) {
    $devnull = fakeDevNull(['https://dev-null.test/api/v1/users/albertoarena/commits' => [200, $data]]);

    expect($devnull->posts('albertoarena'))->toBeNull();
})->with([
    'no data' => [['meta' => []]],
    'a commit without a body' => [['data' => [['id' => 'c1', 'parent_id' => null, 'html_url' => 'https://dev-null.test/x', 'created_at' => '2026-10-17T00:00:00Z']]]],
    'a commit with a bad date' => [['data' => [commit('c1', 'https://phplives.dev', 'yesterday at noon-ish')]]],
    'a link that is not HTTPS' => [['data' => [['html_url' => 'javascript:alert(1)'] + commit('c1', 'https://phplives.dev', '2026-10-17T00:00:00Z')]]],
]);
