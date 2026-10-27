<?php

declare(strict_types=1);

namespace PhpLives;

use Closure;
use DateTimeImmutable;

/**
 * Reads the public /dev/null API at build time. It never throws. When the API is
 * down, or answers with something it cannot read, a lookup gives null. The site
 * then builds without /dev/null.
 */
final readonly class DevNull
{
    public const string URL = 'https://dev-null.sh';

    /** The site's own account. Its posts that link to the site are shown. */
    public const string ACCOUNT = 'albertoarena';

    public const int MAX_PAGES = 5;

    /**
     * The API allows 60 requests a minute, and the build asks once for each maker.
     * When it says too many requests (429), the build waits this long and asks again.
     */
    public const int RETRY_SECONDS = 5;

    /** How many times the build asks again, so it waits a little more than a minute in all. */
    public const int MAX_RETRIES = 14;

    /** @var Closure(string): array{int, mixed} */
    private Closure $get;

    /** @var Closure(int): void */
    private Closure $wait;

    /**
     * @param  (Closure(string): array{int, mixed})|null  $get  Gets a URL, as Http::getJson does.
     * @param  (Closure(int): void)|null  $wait  Waits a number of seconds, as sleep() does.
     */
    public function __construct(
        private string $url = self::URL,
        ?Closure $get = null,
        ?Closure $wait = null,
    ) {
        $this->get = $get ?? Http::getJson(...);
        $this->wait = $wait ?? static function (int $seconds): void {
            sleep($seconds);
        };
    }

    /**
     * Gets a URL. When the API says too many requests, it waits and asks again.
     *
     * @return array{int, mixed}
     */
    private function fetch(string $url): array
    {
        $answer = ($this->get)($url);

        for ($retry = 0; $answer[0] === 429 && $retry < self::MAX_RETRIES; $retry++) {
            ($this->wait)(self::RETRY_SECONDS);
            $answer = ($this->get)($url);
        }

        return $answer;
    }

    /**
     * The usernames that have a /dev/null profile, or null when the API is down.
     *
     * @param  list<string>  $usernames  GitHub usernames, which are also /dev/null usernames.
     * @return list<string>|null
     */
    public function profiles(array $usernames): ?array
    {
        $found = [];
        $seen = [];

        foreach ($usernames as $username) {
            $key = strtolower($username);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            [$status, $data] = $this->fetch("{$this->url}/api/v1/users/".rawurlencode($username));

            if ($status === 404) {
                continue;
            }

            // The API names the user in `handle`. It used `username` before, so both count.
            if ($status !== 200 || ! is_string($data['data']['handle'] ?? $data['data']['username'] ?? null)) {
                return null;
            }

            $found[] = $username;
        }

        return $found;
    }

    /**
     * The newest posts by a user that link to phplives.dev, leaving out replies,
     * or null when the API is down.
     *
     * @return list<Post>|null
     */
    public function posts(string $username, int $limit = 3): ?array
    {
        $posts = [];
        $url = "{$this->url}/api/v1/users/".rawurlencode($username).'/commits';

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            [$status, $data] = $this->fetch($url);

            if ($status !== 200 || ! is_array($data['data'] ?? null)) {
                return null;
            }

            foreach ($data['data'] as $commit) {
                $post = self::post($commit);

                if ($post === null) {
                    return null;
                }

                if (($commit['parent_id'] ?? null) === null && stripos($post->body, 'phplives.dev') !== false) {
                    $posts[] = $post;
                }
            }

            $cursor = $data['meta']['next_cursor'] ?? null;

            if (count($posts) >= $limit || ! is_string($cursor)) {
                break;
            }

            $url = strtok($url, '?').'?cursor='.rawurlencode($cursor);
        }

        usort($posts, fn (Post $a, Post $b) => $b->posted <=> $a->posted);

        return array_slice($posts, 0, $limit);
    }

    private static function post(mixed $commit): ?Post
    {
        $url = $commit['html_url'] ?? null;
        $body = $commit['body'] ?? null;
        $created = $commit['created_at'] ?? null;

        if (! is_string($url) || ! str_starts_with($url, 'https://') || ! is_string($body) || ! is_string($created)) {
            return null;
        }

        $posted = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:sP', $created);

        return $posted === false ? null : new Post($url, trim($body), $posted);
    }
}
