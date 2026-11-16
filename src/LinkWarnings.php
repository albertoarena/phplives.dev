<?php

declare(strict_types=1);

namespace PhpLives;

/**
 * Counts how many runs in a row each link has only warned, so a site that stays
 * down still gets noticed. The count is kept in a JSON file between runs.
 */
final class LinkWarnings
{
    /** A link that warns on this many runs in a row counts as broken. */
    public const int RUNS_TO_FAIL = 2;

    /** @var array<string, int> */
    private array $now = [];

    /**
     * @param  array<string, int>  $before
     */
    private function __construct(private readonly array $before) {}

    /**
     * Reads the counts of the run before. A missing or broken file starts again.
     */
    public static function load(string $file): self
    {
        $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        if (! is_array($data) || array_is_list($data)) {
            return new self([]);
        }

        return new self(array_filter($data, is_int(...)));
    }

    /**
     * Records that a link warned on this run, and gives how many runs in a row it has.
     */
    public function warn(string $url): int
    {
        return $this->now[$url] ??= ($this->before[$url] ?? 0) + 1;
    }

    /**
     * Writes the counts of this run. A link that did not warn is left out.
     */
    public function save(string $file): void
    {
        file_put_contents($file, json_encode((object) $this->now, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }
}
