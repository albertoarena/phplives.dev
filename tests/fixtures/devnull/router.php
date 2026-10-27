<?php

/**
 * A fake /dev/null API for the tests, served with `php -S`.
 */

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$users = ['nunomaduro', 'albertoarena'];

$commits = [
    [
        'id' => 'c2',
        'body' => "Shipped with PHP: Pest 5.\n\nSee more at https://phplives.dev",
        'parent_id' => null,
        'html_url' => 'https://dev-null.sh/@albertoarena/commits/c2',
        'created_at' => '2026-10-16T09:00:00Z',
    ],
    [
        'id' => 'c1',
        'body' => 'Hello from Sicily',
        'parent_id' => null,
        'html_url' => 'https://dev-null.sh/@albertoarena/commits/c1',
        'created_at' => '2026-10-15T09:00:00Z',
    ],
];

header('Content-Type: application/json');

if ($path === '/moved') {
    header('Location: /api/v1/users/nunomaduro', true, 302);

    return;
}

if ($path === '/head-only') {
    http_response_code($_SERVER['REQUEST_METHOD'] === 'HEAD' ? 204 : 405);

    return;
}

if ($path === '/broken') {
    http_response_code(500);
    echo '{"message": "Server Error"}';

    return;
}

if ($path === '/not-json') {
    echo '<html>Not JSON</html>';

    return;
}

if ($path === '/api/v1/users/albertoarena/commits') {
    echo json_encode(['data' => $commits, 'meta' => ['next_cursor' => null]]);

    return;
}

if (preg_match('#^/api/v1/users/([^/]+)$#', $path, $match) && in_array($match[1], $users, true)) {
    echo json_encode(['data' => ['handle' => $match[1]]]);

    return;
}

http_response_code(404);
echo '{"message": "Not found"}';
