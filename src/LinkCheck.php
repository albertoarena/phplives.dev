<?php

declare(strict_types=1);

namespace PhpLives;

use Closure;

/**
 * Checks that the links in an entry still work.
 */
final readonly class LinkCheck
{
    /** How long to wait before asking again a link that only warned. */
    public const int RETRY_SECONDS = 5;

    /** @var Closure(string, string): array{int, ?string} */
    private Closure $status;

    /** @var Closure(int): void */
    private Closure $wait;

    /**
     * @param  (Closure(string, string): array{int, ?string})|null  $status  Gets a URL with a method, as Http::answer does.
     * @param  (Closure(int): void)|null  $wait  Waits a number of seconds, as sleep() does.
     */
    public function __construct(?Closure $status = null, ?Closure $wait = null)
    {
        $this->status = $status ?? Http::answer(...);
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
     * The answer a link gives. A server that fails or gives no answer may only
     * have a problem for a moment, so the link gets one more try after a wait.
     */
    public function status(string $url): LinkStatus
    {
        $status = $this->ask($url);

        if ($status->warns()) {
            ($this->wait)(self::RETRY_SECONDS);
            $status = $this->ask($url);
        }

        return $status;
    }

    /**
     * Some servers refuse a HEAD request, so a link that fails with HEAD gets a
     * second try with GET.
     */
    private function ask(string $url): LinkStatus
    {
        $status = new LinkStatus(...($this->status)($url, 'HEAD'));

        return $status->ok() ? $status : new LinkStatus(...($this->status)($url, 'GET'));
    }
}
