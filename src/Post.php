<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;

/**
 * One commit on /dev/null.
 */
final readonly class Post
{
    public function __construct(
        public string $url,
        public string $body,
        public DateTimeImmutable $posted,
    ) {}
}
