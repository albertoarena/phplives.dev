<?php

declare(strict_types=1);

namespace PhpLives;

/**
 * A group of entries on a list page: one week, or one month.
 */
final readonly class Bundle
{
    /**
     * @param  string  $id  A stable id for the page, such as week-2026-43 or month-2026-09.
     * @param  'week'|'month'  $kind
     * @param  list<Entry>  $entries  Newest first.
     */
    public function __construct(
        public string $id,
        public string $kind,
        public string $title,
        public array $entries,
    ) {}
}
