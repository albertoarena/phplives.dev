<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;
use XMLWriter;

/**
 * Renders the static pages from the entries.
 */
final readonly class Site
{
    private const string TEMPLATES = __DIR__.'/../templates';

    private const string ASSETS = __DIR__.'/../assets';

    /** The brand art: the icon, the share card and the README badge. TRADEMARK.md covers it. */
    private const string ART = __DIR__.'/../art';

    /** With more stacks or topics than this, the filter is a dropdown instead of chips. */
    public const int MAX_CHIPS = 6;

    /** A topic shows in a filter, and gets its own feed, only with this many entries. */
    public const int MIN_TOPIC_ENTRIES = 2;

    /** The Cloudflare Web Analytics site token. It is public: every page carries it. */
    public const string ANALYTICS_TOKEN = '7f3f9ca2fbab49c49f067f46832153d9';

    public const string URL = 'https://phplives.dev';

    /**
     * The switch for entry pages. When it is on, each entry has its own page, the
     * sitemap lists each one, the feed links to it, and the lists link to it.
     */
    public const bool ENTRY_PAGES = true;

    /** @var array<string, true> Lower-case GitHub usernames that have a /dev/null profile. */
    private array $devnullUsers;

    /** True when each entry page has its own share card. See ShareCard. */
    private bool $shareCards;

    /** True when each entry page offers its QR code as a PNG file too. See QrCode. */
    private bool $qrPng;

    /**
     * @param  list<string>  $devnullUsers  GitHub usernames with a /dev/null profile.
     * @param  list<Post>  $posts  The site account's /dev/null posts that link to the site.
     * @param  bool  $entryPages  True when the site has a page for each entry. See ENTRY_PAGES.
     * @param  bool|null  $shareCards  False to share entry pages with the site card. Null to draw
     *                                 a card for each entry page when GD can.
     * @param  bool|null  $qrPng  False to offer the QR code as SVG only. Null to write the PNG file
     *                            too when GD can.
     */
    public function __construct(
        private Entries $entries,
        private DateTimeImmutable $today,
        array $devnullUsers = [],
        private array $posts = [],
        private bool $entryPages = self::ENTRY_PAGES,
        ?bool $shareCards = null,
        ?bool $qrPng = null,
    ) {
        $this->devnullUsers = array_fill_keys(array_map(strtolower(...), $devnullUsers), true);
        $this->shareCards = $entryPages && ($shareCards ?? ShareCard::available());
        $this->qrPng = $entryPages && ($qrPng ?? QrCode::pngAvailable());
    }

    /**
     * @return array<string, string> HTML, keyed by its path in the output folder.
     */
    public function pages(): array
    {
        $cutoff = Format::date(Entries::cutoff($this->today));
        $current = $this->entries->current();
        $featured = Bundles::featured($current, $this->today);
        $rest = array_values(array_filter($current, fn (Entry $e) => ! in_array($e, $featured, true)));
        [$latestTitle, $latestNote] = $this->latestHead($current, count($featured));

        return [
            'index.html' => $this->page('main', 'phplives.dev: PHP delivers. PHP lives!', '/', [
                'entries' => $current,
                'featured' => $featured,
                // The newest parcel is the entry that shipped last, wherever it sits on the page.
                'newestSlug' => $current[0]->slug ?? null,
                'latestTitle' => $latestTitle,
                'latestNote' => $latestNote,
                'bundles' => Bundles::group($rest, $this->today),
                'cutoff' => $cutoff,
                'stacks' => $this->stackSummary($current),
                'archivedCount' => count($this->entries->archived()),
                'posts' => $this->posts,
                'copyScript' => trim(file_get_contents(self::ASSETS.'/copy.js')),
                'counts' => $this->stackCounts($current),
                'topics' => $this->topicCounts($current),
                'manifestScript' => $this->script('manifest.js'),
                'weekScript' => $this->script('week.js'),
            ]),
            'archive/index.html' => $this->page('archive', 'phplives.dev: archive', '/archive/', [
                'entries' => $this->entries->archived(),
                'bundles' => Bundles::months($this->entries->archived()),
                'cutoff' => $cutoff,
                'counts' => $this->stackCounts($this->entries->archived()),
                'topics' => $this->topicCounts($this->entries->archived()),
                'manifestScript' => $this->script('manifest.js'),
            ]),
            'privacy/index.html' => $this->page('privacy', 'phplives.dev: privacy', '/privacy/', []),
            'credits/index.html' => $this->page('credits', 'phplives.dev: credits', '/credits/', []),
            'feeds/index.html' => $this->page('feeds', 'phplives.dev: feeds', '/feeds/', [
                'counts' => $this->stackCounts($this->entries->all()),
                'topics' => $this->topicCounts($this->entries->all()),
                'copyScript' => $this->script('copy.js'),
            ]),
            '404.html' => $this->page('not-found', 'phplives.dev: page not found', null, []),
        ] + $this->entryPages();
    }

    /**
     * One page for each entry, current or archived.
     *
     * @return array<string, string>
     */
    private function entryPages(): array
    {
        if (! $this->entryPages) {
            return [];
        }

        $pages = [];
        $all = $this->entries->all();
        $newest = $this->entries->current()[0] ?? null;
        $archived = $this->entries->archived();
        // A topic tag links to the main page filtered by it, when that filter has it.
        $filterTopics = array_keys($this->topicCounts($this->entries->current()));

        foreach ($all as $index => $entry) {
            $pages[ltrim(self::entryPath($entry), '/').'index.html'] = $this->page(
                'entry-page',
                "{$entry->name}: shipped with PHP | phplives.dev",
                self::entryPath($entry),
                [
                    'entry' => $entry,
                    'newest' => $entry === $newest,
                    'archived' => in_array($entry, $archived, true),
                    'archivedOn' => in_array($entry, $archived, true) ? Entries::archivedOn($entry->shipped) : null,
                    'before' => $all[$index + 1] ?? null,
                    'after' => $all[$index - 1] ?? null,
                    'url' => self::URL.self::entryPath($entry),
                    'shareTitle' => self::shareTitle($entry),
                    'badge' => Markdown::badge($entry),
                    'qr' => QrCode::forEntry($entry),
                    'qrPng' => $this->qrPng,
                    'copyScript' => $this->script('copy.js'),
                    'shareScript' => $this->script('share.js'),
                    'filterTopics' => $filterTopics,
                ],
                ['description' => $entry->sentence, 'markdown' => Markdown::entryPath($entry), 'shareTitle' => self::shareTitle($entry)]
                    + (in_array($entry, $archived, true) ? ['section' => '/archive/'] : [])
                    + ($this->shareCards ? ['image' => ShareCard::url($entry), 'imageAlt' => ShareCard::alt($entry)] : []),
            );
        }

        return $pages;
    }

    /**
     * True when each entry has its own page. See ENTRY_PAGES.
     */
    public function hasEntryPages(): bool
    {
        return $this->entryPages;
    }

    /**
     * True when each entry page has its own share card. See ShareCard.
     */
    public function hasShareCards(): bool
    {
        return $this->shareCards;
    }

    /**
     * The pages, and the files that help people and tools find them: robots.txt, the
     * sitemap, llms.txt, the Atom feeds, a Markdown copy and the QR code of each entry, and the headers
     * that Cloudflare serves them with.
     *
     * @return array<string, string> Each file's content, keyed by its path in the output folder.
     */
    public function files(): array
    {
        $files = $this->pages() + [
            'robots.txt' => $this->robots(),
            'sitemap.xml' => $this->sitemap(),
            'llms.txt' => Markdown::llms($this->entries),
        ];

        $feed = new Feed($this->entries->all(), $this->today, $this->entryPages);
        $files['feed.xml'] = $feed->xml('/feed.xml');

        foreach (array_keys($this->stackCounts($this->entries->all())) as $stack) {
            $files["feed/{$stack}.xml"] = $feed->xml("/feed/{$stack}.xml", $stack);
        }

        foreach (array_keys($this->topicCounts($this->entries->all())) as $topic) {
            $files["feed/topic/{$topic}.xml"] = $feed->xml("/feed/topic/{$topic}.xml", topic: $topic);
        }

        foreach ($this->entries->all() as $entry) {
            $files[ltrim(Markdown::entryPath($entry), '/')] = Markdown::entry($entry);

            if ($this->entryPages) {
                $files[ltrim(QrCode::svgPath($entry), '/')] = QrCode::svg($entry);
            }
        }

        return $files + ['_headers' => self::headers()];
    }

    /**
     * The address of an entry's own page, from the site's root. See ENTRY_PAGES.
     */
    public static function entryPath(Entry $entry): string
    {
        return "/entries/{$entry->slug}/";
    }

    /**
     * Where a stub's link goes: the entry's own page, or null while the site has no
     * entry pages. See ENTRY_PAGES.
     */
    public function entryLink(Entry $entry): ?string
    {
        return $this->entryPages ? self::entryPath($entry) : null;
    }

    /**
     * The heading and the note above the full labels. They say how many of them were
     * added this week, so a visitor knows why an older project has a full label.
     *
     * @param  list<Entry>  $entries
     * @return array{0: string, 1: string}
     */
    private function latestHead(array $entries, int $shown): array
    {
        $added = Bundles::addedThisWeek($entries, $this->today);
        $since = 'since Monday '.Bundles::weekStart($this->today)->format('d/m');

        return match (true) {
            $added === 0 => ['Added last', "Nothing has been added {$since}. ".($shown === 1 ? 'This one was' : "These {$shown} were").' added last.'],
            $added < $shown => ['This week', ($added === 1 ? '1 parcel has' : "{$added} parcels have")." been added {$since}. The rest were added before."],
            $added === $shown => ['This week', ($shown === 1 ? 'It has' : "All {$shown} have")." been added {$since}."],
            default => ['This week', "{$added} parcels have been added {$since}. Here are the newest {$shown}. The others are in the bundles below, by shipped date."],
        };
    }

    /**
     * Writes every page, file and asset into a folder.
     */
    public function write(string $outDir): void
    {
        foreach ($this->files() as $path => $content) {
            $file = rtrim($outDir, '/').'/'.$path;

            if (! is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }

            file_put_contents($file, $content);
        }

        copy(self::ASSETS.'/site.css', rtrim($outDir, '/').'/site.css');
        copy(self::ART.'/favicon.svg', rtrim($outDir, '/').'/favicon.svg');
        copy(self::ART.'/share-card.png', rtrim($outDir, '/').'/share.png');
        copy(self::ART.'/badge.svg', rtrim($outDir, '/').'/badge.svg');

        foreach (['fonts', 'img'] as $dir) {
            if (! is_dir(rtrim($outDir, '/')."/{$dir}")) {
                mkdir(rtrim($outDir, '/')."/{$dir}", 0777, true);
            }

            // Only the files: the folders inside, such as the card fonts, are for the build.
            foreach (array_filter(glob(self::ASSETS."/{$dir}/*"), is_file(...)) as $file) {
                copy($file, rtrim($outDir, '/')."/{$dir}/".basename($file));
            }
        }

        if ($this->shareCards) {
            $this->writeShareCards($outDir);
        }

        if ($this->qrPng) {
            foreach ($this->entries->all() as $entry) {
                file_put_contents(rtrim($outDir, '/').QrCode::pngPath($entry), QrCode::png($entry));
            }
        }
    }

    /**
     * Draws the share card of each entry, current or archived, next to its page.
     */
    private function writeShareCards(string $outDir): void
    {
        $card = new ShareCard();

        foreach ($this->entries->all() as $entry) {
            file_put_contents(rtrim($outDir, '/').ShareCard::path($entry), $card->png($entry));
        }
    }

    /**
     * Every crawler may read every page. The content signals are Cloudflare's: search
     * engines may index the site, and AI tools may use it to answer a question. The
     * signal for AI training is not set.
     */
    private function robots(): string
    {
        return "User-agent: *\nContent-Signal: search=yes, ai-input=yes, ai-train=yes\nAllow: /\n\nSitemap: ".self::URL."/sitemap.xml\n";
    }

    /**
     * Every page that search engines should index. The list pages change when an entry
     * is added or checked again, so their date is the newest of those dates. The
     * privacy page, the credits and the feeds page change by hand, so they have no date.
     */
    private function sitemap(): string
    {
        $dates = [];

        foreach ($this->entries->all() as $entry) {
            array_push($dates, $entry->added, $entry->checked);
        }

        $newest = $dates === [] ? null : max($dates);

        $urls = [
            ['/', $newest],
            ['/archive/', $newest],
            ['/privacy/', null],
            ['/credits/', null],
            ['/feeds/', null],
        ];

        if ($this->entryPages) {
            foreach ($this->entries->all() as $entry) {
                $urls[] = [self::entryPath($entry), $entry->checked];
            }
        }

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('  ');
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElementNs(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as [$path, $lastmod]) {
            $xml->startElement('url');
            $xml->writeElement('loc', self::URL.$path);

            if ($lastmod !== null) {
                $xml->writeElement('lastmod', $lastmod->format('Y-m-d'));
            }

            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Cloudflare picks a file's type from its extension, and it reads .xml as plain
     * XML. The feeds are Atom. The splat in /feed/* also matches the topic feeds in
     * /feed/topic/.
     */
    private static function headers(): string
    {
        $atom = '  Content-Type: application/atom+xml; charset=utf-8';

        return "/feed.xml\n{$atom}\n/feed/*\n{$atom}\n";
    }

    /**
     * @param  string|null  $path  The page's own address. Null for the page that answers missing addresses.
     * @param  array{description?: string, markdown?: string, shareTitle?: string, image?: string, imageAlt?: string}  $head
     *         The page's own description, its Markdown copy, its title on share cards, and its own share card with its text.
     */
    private function page(string $template, string $title, ?string $path, array $vars, array $head = []): string
    {
        return self::externalLinksInNewTab($this->render('layout', $head + [
            'description' => null,
            'markdown' => null,
            'shareTitle' => null,
            'image' => null,
            'imageAlt' => null,
            'title' => $title,
            'path' => $path,
            'section' => $path,
            'today' => Format::date($this->today),
            'themeScript' => trim(file_get_contents(self::ASSETS.'/theme.js')),
            'analyticsToken' => self::ANALYTICS_TOKEN,
            'content' => $this->render($template, $vars + ['today' => Format::date($this->today)]),
        ]));
    }

    /**
     * An entry's title on share cards. Cards already show the site's name, so it is left out.
     */
    private static function shareTitle(Entry $entry): string
    {
        return "{$entry->name}: shipped with PHP";
    }

    /**
     * Every link that leaves the site opens in a new tab, so the visitor keeps the page.
     * Links between the site's own pages stay in the same tab.
     */
    private static function externalLinksInNewTab(string $html): string
    {
        return preg_replace('#<a\b(?![^>]*\btarget=)([^>]*\bhref="https://[^"]*"[^>]*)>#', '<a$1 target="_blank" rel="noopener">', $html);
    }

    private function render(string $template, array $vars): string
    {
        $e = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $site = $this;

        ob_start();
        (static function () use ($template, $vars, $e, $site) {
            extract($vars);
            require self::TEMPLATES."/{$template}.php";
        })();

        return ob_get_clean();
    }

    /**
     * The stack or topic filter for some entries: chips for a few choices, a dropdown for many.
     *
     * @param  array<string, int>  $counts  Entries per stack or topic, most common first.
     * @param  string  $kind  'stack' or 'topic'.
     * @param  int|null  $total  The entries on the page, for "All". Null for the sum of the counts.
     */
    public function filterControl(array $counts, int $maxChips = self::MAX_CHIPS, string $kind = 'stack', ?int $total = null): string
    {
        return $this->render('filter', [
            'counts' => $counts,
            'maxChips' => $maxChips,
            'kind' => $kind,
            'total' => $total ?? array_sum($counts),
        ]);
    }

    /**
     * Entries per topic, most common first, for the topics with at least
     * MIN_TOPIC_ENTRIES entries. Topics with the same count keep the order of
     * EntryValidator::TOPICS.
     *
     * @param  list<Entry>  $entries
     * @return array<string, int>
     */
    private function topicCounts(array $entries): array
    {
        $counts = [];

        foreach (EntryValidator::TOPICS as $topic) {
            $count = count(array_filter($entries, fn (Entry $e) => in_array($topic, $e->topics, true)));

            if ($count >= self::MIN_TOPIC_ENTRIES) {
                $counts[$topic] = $count;
            }
        }

        $order = array_flip(EntryValidator::TOPICS);
        uksort($counts, fn (string $a, string $b) => [$counts[$b], $order[$a]] <=> [$counts[$a], $order[$b]]);

        return $counts;
    }

    /**
     * Entries per stack, most common first. Stacks with the same count keep the order of
     * EntryValidator::STACKS.
     *
     * @param  list<Entry>  $entries
     * @return array<string, int>
     */
    private function stackCounts(array $entries): array
    {
        $counts = [];

        foreach (EntryValidator::STACKS as $stack) {
            $count = count(array_filter($entries, fn (Entry $e) => $e->stack === $stack));

            if ($count > 0) {
                $counts[$stack] = $count;
            }
        }

        $order = array_flip(EntryValidator::STACKS);
        uksort($counts, fn (string $a, string $b) => [$counts[$b], $order[$a]] <=> [$counts[$a], $order[$b]]);

        return $counts;
    }

    private function script(string $file): string
    {
        return trim(file_get_contents(self::ASSETS.'/'.$file));
    }

    public function hasDevnull(Maker $maker): bool
    {
        return $maker->github !== null && isset($this->devnullUsers[strtolower($maker->github)]);
    }

    /**
     * The number on an entry's label: its ship date, then its place among all
     * entries in the order they shipped.
     */
    public function trackingNumber(Entry $entry): string
    {
        $oldestFirst = array_reverse($this->entries->all());
        $position = array_search($entry, $oldestFirst, true) + 1;

        return $entry->shipped->format('Ymd').str_pad((string) $position, 2, '0', STR_PAD_LEFT);
    }

    /**
     * @param  list<Entry>  $entries
     */
    private function stackSummary(array $entries): string
    {
        $counts = [];

        foreach (EntryValidator::STACKS as $stack) {
            $count = count(array_filter($entries, fn (Entry $e) => $e->stack === $stack));

            if ($count > 0) {
                $counts[] = $count.' '.Format::stackLabel($stack);
            }
        }

        return implode(', ', $counts);
    }
}
