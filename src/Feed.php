<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;
use XMLWriter;

/**
 * An Atom feed of the newest entries, in the order they were added to the site.
 */
final readonly class Feed
{
    /** A feed reader only needs the newest entries. */
    public const int MAX_ENTRIES = 50;

    /**
     * @param  list<Entry>  $entries  Every entry the feed can show, in any order.
     * @param  bool  $entryPages  True when the site has a page for each entry. Feed entries then link to it.
     */
    public function __construct(
        private array $entries,
        private DateTimeImmutable $today,
        private bool $entryPages,
    ) {}

    /**
     * The id of a feed entry. It never changes, so a reader never shows an entry twice.
     */
    public static function entryId(Entry $entry): string
    {
        return "tag:phplives.dev,2026:{$entry->slug}";
    }

    /**
     * @param  string  $path  The feed's own address on the site.
     * @param  string|null  $stack  Only the entries of this stack. Null for every entry.
     */
    public function xml(string $path, ?string $stack = null): string
    {
        $entries = $this->newest($stack);

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('  ');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElementNs(null, 'feed', 'http://www.w3.org/2005/Atom');
        $xml->writeElement('title', $stack === null ? 'phplives.dev' : 'phplives.dev: '.Format::stackLabel($stack));
        $xml->writeElement('subtitle', 'PHP delivers. PHP lives!');
        $xml->writeElement('id', 'tag:phplives.dev,2026:feed'.($stack === null ? '' : "/{$stack}"));
        $xml->writeElement('updated', self::time($entries === [] ? $this->today : $entries[0]->added));
        self::link($xml, 'self', Site::URL.$path, 'application/atom+xml');
        self::link($xml, 'alternate', Site::URL.'/', 'text/html');

        foreach ($entries as $entry) {
            $this->entry($xml, $entry);
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * @return list<Entry> Newest added first, then newest shipped first.
     */
    private function newest(?string $stack): array
    {
        $entries = array_filter($this->entries, fn (Entry $e) => $stack === null || $e->stack === $stack);
        usort($entries, fn (Entry $a, Entry $b) => [$b->added, $b->shipped, $a->slug] <=> [$a->added, $a->shipped, $b->slug]);

        return array_slice($entries, 0, self::MAX_ENTRIES);
    }

    private function entry(XMLWriter $xml, Entry $entry): void
    {
        $xml->startElement('entry');
        $xml->writeElement('id', self::entryId($entry));
        $xml->writeElement('title', $entry->name);
        $xml->writeElement('published', self::time($entry->added));
        $xml->writeElement('updated', self::time($entry->added));
        self::link($xml, 'alternate', $this->entryPages ? Site::URL.Site::entryPath($entry) : $entry->source);
        $xml->writeElement('summary', "{$entry->sentence} Shipped ".Format::date($entry->shipped).'. Stack: '.Format::stackLabel($entry->stack).'.');
        $xml->startElement('content');
        $xml->writeAttribute('type', 'html');
        $xml->text($this->content($entry));
        $xml->endElement();

        foreach ($entry->makers as $maker) {
            $xml->startElement('author');
            $xml->writeElement('name', $maker->name);

            if ($maker->github !== null) {
                $xml->writeElement('uri', "https://github.com/{$maker->github}");
            }

            $xml->endElement();
        }

        $xml->startElement('category');
        $xml->writeAttribute('term', $entry->stack);
        $xml->writeAttribute('label', Format::stackLabel($entry->stack));
        $xml->endElement();
        $xml->endElement();
    }

    /**
     * The facts of the entry as HTML, so a feed reader can show the entry without the site.
     * Only what the entry file says.
     */
    private function content(Entry $entry): string
    {
        $e = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $credit = Format::credit($entry->makers, fn (Maker $maker) => $maker->github === null
            ? $e($maker->name)
            : '<a href="https://github.com/'.$e($maker->github).'">'.$e($maker->name).'</a>');
        $links = '<a href="'.$e($entry->source).'">Source</a>'
            .($this->entryPages ? ' · <a href="'.$e(Site::URL.Site::entryPath($entry)).'">On phplives.dev</a>' : '');

        return '<p>'.$e($entry->sentence).'</p>'
            .($entry->details === null ? '' : '<p>'.$e($entry->details).'</p>')
            .'<p>Made by '.$credit.'. Shipped '.Format::date($entry->shipped).'. Stack: '.$e(Format::stackLabel($entry->stack)).'.</p>'
            .($entry->package === null ? '' : '<p>Install: <code>composer require '.$e($entry->package).'</code></p>')
            .'<p>'.$links.'</p>';
    }

    private static function link(XMLWriter $xml, string $rel, string $href, ?string $type = null): void
    {
        $xml->startElement('link');
        $xml->writeAttribute('rel', $rel);

        if ($type !== null) {
            $xml->writeAttribute('type', $type);
        }

        $xml->writeAttribute('href', $href);
        $xml->endElement();
    }

    /**
     * Entry dates are whole days, so a feed time is the start of the day in UTC.
     */
    private static function time(DateTimeImmutable $day): string
    {
        return $day->format('Y-m-d').'T00:00:00Z';
    }
}
