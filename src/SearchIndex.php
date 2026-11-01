<?php

declare(strict_types=1);

namespace PhpLives;

/**
 * The words the search field looks in, one string per entry. Each page carries the
 * index of its own entries, and assets/manifest.js matches what the visitor types
 * against it. The script normalises the text, so this class only joins the fields.
 */
final class SearchIndex
{
    /**
     * @param  list<Entry>  $entries
     * @return array<string, string> The words of each entry, keyed by its slug.
     */
    public static function for(array $entries): array
    {
        $index = [];

        foreach ($entries as $entry) {
            $index[$entry->slug] = self::words($entry);
        }

        return $index;
    }

    /**
     * The index as JSON that is safe inside a script tag.
     *
     * @param  list<Entry>  $entries
     */
    public static function json(array $entries): string
    {
        return json_encode(
            (object) self::for($entries),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP,
        );
    }

    /**
     * Name, sentence, details, makers with their GitHub usernames, package, stack and
     * topics. Not the devnull text, which repeats the sentence, and not the links.
     */
    private static function words(Entry $entry): string
    {
        $parts = [$entry->name, $entry->sentence, $entry->details];

        foreach ($entry->makers as $maker) {
            $parts[] = $maker->name;
            $parts[] = $maker->github;
        }

        $parts[] = $entry->package;
        $parts[] = Format::stackLabel($entry->stack);

        foreach ($entry->topics as $topic) {
            $parts[] = Format::topicLabel($topic);
        }

        return implode(' ', array_filter($parts, fn (?string $part) => $part !== null && $part !== ''));
    }
}
