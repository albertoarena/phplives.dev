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

    /** @var Closure(string): array{int, mixed} */
    private Closure $get;

    /**
     * @param  (Closure(string): array{int, mixed})|null  $get  Gets a URL, as Http::getJson does.
     */
    public function __construct(
        private string $url = self::URL,
        ?Closure $get = null,
    ) {
        $this->get = $get ?? Http::getJson(...);
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
            [$status, $data] = ($this->get)("{$this->url}/api/v1/users/".rawurlencode($username));

            if ($status === 404) {
                continue;
            }

            if ($status !== 200 || ! is_string($data['data']['username'] ?? null)) {
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
            [$status, $data] = ($this->get)($url);

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
