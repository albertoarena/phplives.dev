<?php

declare(strict_types=1);

namespace PhpLives;

final class Http
{
    public const int TIMEOUT_SECONDS = 5;

    /**
     * Gets a URL and decodes its JSON body.
     *
     * @return array{int, mixed} The status (0 when nothing answered) and the data
     *                           (null when the body is not JSON).
     */
    public static function getJson(string $url): array
    {
        [$status, $body] = self::request($url, 'GET', 'application/json');

        return [$status, $body === null ? null : json_decode($body, true)];
    }

    /**
     * The status a URL answers with, after redirects. 0 when nothing answered.
     */
    public static function status(string $url, string $method = 'GET'): int
    {
        return self::request($url, $method, '*/*')[0];
    }

    /**
     * @return array{int, ?string}
     */
    private static function request(string $url, string $method, string $accept): array
    {
        $context = stream_context_create(['http' => [
            'method' => $method,
            'timeout' => self::TIMEOUT_SECONDS,
            'ignore_errors' => true,
            'header' => "Accept: {$accept}\r\nUser-Agent: phplives.dev build\r\n",
        ]]);

        // A failed request is an answer here, not an error: it gives status 0.
        set_error_handler(static fn (): bool => true);

        try {
            $body = file_get_contents($url, false, $context);
        } finally {
            restore_error_handler();
        }

        if ($body === false) {
            return [0, null];
        }

        // After a redirect, the headers hold one status line per answer. The last one counts.
        $statuses = preg_grep('#^HTTP/\S+ \d{3}#', http_get_last_response_headers() ?? []);
        preg_match('#^HTTP/\S+ (\d{3})#', (string) end($statuses), $match);

        return [(int) ($match[1] ?? 0), $body];
    }
}
