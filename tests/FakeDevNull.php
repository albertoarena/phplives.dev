<?php

declare(strict_types=1);

namespace Tests;

use RuntimeException;

/**
 * Runs the fake /dev/null API in tests/fixtures/devnull on a free local port.
 */
final class FakeDevNull
{
    private static mixed $process = null;

    private static string $url = '';

    public static function url(): string
    {
        if (self::$process === null) {
            self::start();
        }

        return self::$url;
    }

    /**
     * A local address where nothing listens, so every request fails at once.
     */
    public static function deadUrl(): string
    {
        return 'http://127.0.0.1:'.self::freePort();
    }

    private static function start(): void
    {
        $port = self::freePort();
        $command = [PHP_BINARY, '-S', "127.0.0.1:{$port}", __DIR__.'/fixtures/devnull/router.php'];

        self::$process = proc_open($command, [['pipe', 'r'], ['file', '/dev/null', 'w'], ['file', '/dev/null', 'w']], $pipes);
        self::$url = "http://127.0.0.1:{$port}";

        register_shutdown_function(static fn () => proc_terminate(self::$process));

        set_error_handler(static fn (): bool => true);

        try {
            for ($tries = 0; $tries < 100; $tries++) {
                if ($socket = fsockopen('127.0.0.1', $port, $errno, $error, 0.1)) {
                    fclose($socket);

                    return;
                }

                usleep(50_000);
            }
        } finally {
            restore_error_handler();
        }

        throw new RuntimeException('The fake /dev/null API did not start.');
    }

    private static function freePort(): int
    {
        $server = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);
        fclose($server);

        return $port;
    }
}
