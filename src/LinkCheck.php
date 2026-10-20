<?php

declare(strict_types=1);

namespace PhpLives;

use Closure;

/**
 * Checks that the links in an entry still work.
 */
final readonly class LinkCheck
{
    /** @var Closure(string, string): int */
    private Closure $status;

    /**
     * @param  (Closure(string, string): int)|null  $status  Gets a URL with a method, as Http::status does.
     */
    public function __construct(?Closure $status = null)
    {
        $this->status = $status ?? Http::status(...);
    }

    /**
     * The links the site shows for an entry, and the links in its /dev/null post.
     *
     * @return list<string>
     */
    public static function links(Entry $entry): array
    {
        $links = [$entry->proofUrl, $entry->source];

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
     * The status a link answers with. Some servers refuse a HEAD request, so a
     * link that fails with HEAD gets a second try with GET.
     */
    public function status(string $url): int
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
