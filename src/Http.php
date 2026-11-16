<?php

declare(strict_types=1);

namespace PhpLives;

final class Http
{
    public const int TIMEOUT_SECONDS = 5;

    /** Why a request failed: nothing answered, or it timed out or was refused. */
    public const string NO_ANSWER = 'no answer';

    /** Why a request failed: the domain name does not resolve. */
    public const string UNKNOWN_HOST = 'unknown host';

    /** Why a request failed: the HTTPS connection or its certificate is not valid. */
    public const string TLS_ERROR = 'TLS error';

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
     * The status a URL answers with, after redirects, and why it failed when
     * nothing answered.
     *
     * @return array{int, ?string} The status (0 when nothing answered) and the
     *                             reason (null when something answered).
     */
    public static function answer(string $url, string $method = 'GET'): array
    {
        [$status, , $failure] = self::request($url, $method, '*/*');

        return [$status, $failure];
    }

    /**
     * Why a request failed, from the warnings PHP gave for it.
     */
    public static function failure(string $warnings): string
    {
        return match (true) {
            str_contains($warnings, 'getaddrinfo') && ! str_contains($warnings, 'Temporary failure') => self::UNKNOWN_HOST,
            (bool) preg_match('/SSL|crypto|certificate/i', $warnings) => self::TLS_ERROR,
            default => self::NO_ANSWER,
        };
    }

    /**
     * @return array{int, ?string, ?string} The status, the body and why it failed.
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
        $warnings = [];
        set_error_handler(static function (int $level, string $message) use (&$warnings): bool {
            $warnings[] = $message;

            return true;
        });

        try {
            $body = file_get_contents($url, false, $context);
        } finally {
            restore_error_handler();
        }

        if ($body === false) {
            return [0, null, self::failure(implode("\n", $warnings))];
        }

        // After a redirect, the headers hold one status line per answer. The last one counts.
        $statuses = preg_grep('#^HTTP/\S+ \d{3}#', http_get_last_response_headers() ?? []);
        preg_match('#^HTTP/\S+ (\d{3})#', (string) end($statuses), $match);

        return [(int) ($match[1] ?? 0), $body, null];
    }
}
