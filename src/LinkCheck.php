<?php

declare(strict_types=1);

namespace PhpLives;

use Closure;

/**
 * Checks that the links in an entry still work.
 */
final readonly class LinkCheck
{
    /** How long to wait before asking again a link that gave no answer. */
    public const int RETRY_SECONDS = 5;

    /** @var Closure(string, string): int */
    private Closure $status;

    /** @var Closure(int): void */
    private Closure $wait;

    /**
     * @param  (Closure(string, string): int)|null  $status  Gets a URL with a method, as Http::status does.
     * @param  (Closure(int): void)|null  $wait  Waits a number of seconds, as sleep() does.
     */
    public function __construct(?Closure $status = null, ?Closure $wait = null)
    {
        $this->status = $status ?? Http::status(...);
        $this->wait = $wait ?? static function (int $seconds): void {
            sleep($seconds);
        };
    }

    /**
     * The links the site shows for an entry, and the links in its /dev/null post.
     *
     * @return list<string>
     */
    public static function links(Entry $entry): array
    {
        $links = [$entry->proofUrl, $entry->source];

        if ($entry->docs !== null) {
            $links[] = $entry->docs;
        }

        if ($entry->package !== null) {
            $links[] = Format::packagistUrl($entry->package);
        }

        foreach ($entry->makers as $maker) {
            if ($maker->github !== null) {
                $links[] = "https://github.com/{$maker->github}";
            }
        }

        preg_match_all('#https?://[^\s<>"\'()]+#', $entry->devnull, $matches);

        foreach ($matches[0] as $url) {
            $links[] = rtrim($url, '.,;:!?');
        }

        return array_values(array_unique($links));
    }

    /**
     * The status a link answers with. A server that gives no answer at all may
     * only be slow for a moment, so the link gets one more try after a wait.
     */
    public function status(string $url): int
    {
        $status = $this->ask($url);

        if ($status === 0) {
            ($this->wait)(self::RETRY_SECONDS);
            $status = $this->ask($url);
        }

        return $status;
    }

    /**
     * Some servers refuse a HEAD request, so a link that fails with HEAD gets a
     * second try with GET.
     */
    private function ask(string $url): int
    {
        $status = ($this->status)($url, 'HEAD');

        return self::ok($status) ? $status : ($this->status)($url, 'GET');
    }

    /**
     * A link works when its final answer, after redirects, is a success.
     */
    public static function ok(int $status): bool
    {
        return $status >= 200 && $status < 300;
    }
}
