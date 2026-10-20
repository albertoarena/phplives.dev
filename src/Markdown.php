<?php

declare(strict_types=1);

namespace PhpLives;

/**
 * The Markdown copies of the site, for people and tools that read plain text:
 * llms.txt, and one file for each entry.
 */
final class Markdown
{
    public const string REPO = 'https://github.com/albertoarena/phplives.dev';

    /**
     * The site's summary for language models, in the llms.txt format (llmstxt.org).
     * The rules for what qualifies come from CONTRIBUTING.md.
     */
    public static function llms(Entries $entries): string
    {
        $text = <<<'MD'
            # phplives.dev

            > phplives.dev is a running list of real things people shipped with PHP in the last 6 months. Every entry links to its own source. The line is: PHP delivers. PHP lives!

            A project qualifies when all three are true:

            1. It shipped in the last 6 months. The date comes from the project's own source.
            2. A public link proves that it shipped. Open source projects and closed products both count.
            3. PHP is the main language on its server side.

            Each entry below links to a Markdown copy of its facts.

            ## Entries


            MD;

        $text .= self::list($entries->current());

        if ($entries->archived() !== []) {
            $text .= "\n## Archive\n\n".self::list($entries->archived());
        }

        return $text."\n## Optional\n\n"
            .'- [Source code]('.self::REPO.'): the code that builds this site, under the MIT licence.'."\n"
            .'- [How to contribute]('.self::REPO.'/blob/main/CONTRIBUTING.md): what qualifies, and how to suggest a project or send an entry.'."\n";
    }

    /**
     * The facts of one entry, and nothing that is not in its entry file.
     */
    public static function entry(Entry $entry): string
    {
        $makers = array_map(
            fn (Maker $maker) => $maker->github === null
                ? self::escape($maker->name)
                : self::link($maker->name, "https://github.com/{$maker->github}"),
            $entry->makers,
        );

        return '# '.self::escape($entry->name)."\n\n"
            .self::escape($entry->sentence)."\n\n"
            .'- Made by: '.implode(', ', $makers)."\n"
            .'- Shipped: '.Format::date($entry->shipped)."\n"
            .'- Date from: '.self::link(Format::proofLabel($entry->proofKind), $entry->proofUrl)."\n"
            .'- Source: '.self::link(Format::linkText($entry->source), $entry->source)."\n"
            .'- Stack: '.Format::stackLabel($entry->stack)."\n"
            .'- Checked: '.Format::date($entry->checked)."\n"
            .'- Added: '.Format::date($entry->added)."\n\n"
            .'See more at '.self::link('phplives.dev', Site::URL)."\n";
    }

    /**
     * The address of an entry's Markdown copy, from the site's root.
     */
    public static function entryPath(Entry $entry): string
    {
        return "/entries/{$entry->slug}.md";
    }

    /**
     * @param  list<Entry>  $entries
     */
    private static function list(array $entries): string
    {
        return implode('', array_map(
            fn (Entry $entry) => '- '.self::link($entry->name, Site::URL.self::entryPath($entry)).': '.self::escape($entry->sentence)."\n",
            $entries,
        ));
    }

    private static function link(string $text, string $url): string
    {
        return '['.self::escape($text).']('.str_replace(['(', ')', ' '], ['%28', '%29', '%20'], $url).')';
    }

    /**
     * Entry text is plain text. A character that Markdown reads as markup gets a backslash.
     */
    private static function escape(string $text): string
    {
        return preg_replace('/([\\\\`*_\[\]<>])/', '\\\\$1', $text);
    }
}
