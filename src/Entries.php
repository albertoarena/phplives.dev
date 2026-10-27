<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;

/**
 * All entries, newest first. Entries older than 6 months go to the archive.
 */
final readonly class Entries
{
    public const int MONTHS_ON_MAIN_PAGE = 6;

    /**
     * @param  list<Entry>  $entries
     */
    private function __construct(
        private array $entries,
        private DateTimeImmutable $today,
    ) {}

    /**
     * Reads and checks every entry file in a folder.
     *
     * @throws InvalidEntries when any file has a problem. It lists them all.
     */
    public static function load(string $dir, DateTimeImmutable $today): self
    {
        $validator = new EntryValidator($today);
        $entries = [];
        $slugs = [];
        $errors = [];

        $files = glob(rtrim($dir, '/').'/*.php') ?: [];
        sort($files);

        foreach ($files as $path) {
            $file = basename($path);
            $data = (static fn () => require $path)();

            if (! is_array($data)) {
                $errors[$file] = ['The file must return an array.'];

                continue;
            }

            if ($messages = $validator->errors($data, $file)) {
                $errors[$file] = $messages;

                continue;
            }

            $slug = substr($file, 11, -4);

            if (isset($slugs[$slug])) {
                $errors[$file] = ["The slug {$slug} is already used by {$slugs[$slug]}."];

                continue;
            }

            $slugs[$slug] = $file;
            $entries[] = Entry::fromArray($slug, $data);
        }

        if ($errors) {
            throw new InvalidEntries($errors);
        }

        usort($entries, fn (Entry $a, Entry $b) => [$b->shipped, $a->slug] <=> [$a->shipped, $b->slug]);

        return new self($entries, $today);
    }

    /** @return list<Entry> */
    public function all(): array
    {
        return $this->entries;
    }

    /** @return list<Entry> Entries for the main page. */
    public function current(): array
    {
        $cutoff = self::cutoff($this->today);

        return array_values(array_filter($this->entries, fn (Entry $e) => $e->shipped >= $cutoff));
    }

    /** @return list<Entry> Entries for the archive page. */
    public function archived(): array
    {
        $cutoff = self::cutoff($this->today);

        return array_values(array_filter($this->entries, fn (Entry $e) => $e->shipped < $cutoff));
    }

    /**
     * The oldest ship date that still counts as current: the same day, 6 months
     * back. A day that does not exist in that month moves to the month's last day,
     * so 31/08 gives 28/02 and not 03/03.
     */
    public static function cutoff(DateTimeImmutable $today): DateTimeImmutable
    {
        $firstOfMonth = $today->setTime(0, 0)->modify('first day of')->modify('-'.self::MONTHS_ON_MAIN_PAGE.' months');
        $day = min((int) $today->format('j'), (int) $firstOfMonth->format('t'));

        return $firstOfMonth->setDate((int) $firstOfMonth->format('Y'), (int) $firstOfMonth->format('n'), $day);
    }

    /**
     * The day an entry moves to the archive: the first day whose cutoff is after its
     * ship date. The daily build moves it on that day.
     */
    public static function archivedOn(DateTimeImmutable $shipped): DateTimeImmutable
    {
        $day = $shipped->setTime(0, 0)->modify('+'.(self::MONTHS_ON_MAIN_PAGE - 1).' months');

        while (self::cutoff($day) <= $shipped->setTime(0, 0)) {
            $day = $day->modify('+1 day');
        }

        return $day;
    }
}
