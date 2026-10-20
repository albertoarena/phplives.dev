<?php

declare(strict_types=1);

use Dom\HTMLDocument;
use PhpLives\Entries;
use PhpLives\Site;

beforeEach(function () {
    $this->dir = sys_get_temp_dir().'/phplives-entry-'.bin2hex(random_bytes(4));
    mkdir($this->dir);
    $this->today = new DateTimeImmutable('2026-10-20');
});

afterEach(function () {
    array_map(unlink(...), glob($this->dir.'/*'));
    rmdir($this->dir);
});

/**
 * Every page, parsed, with entry pages on or off.
 *
 * @return array<string, HTMLDocument>
 */
function sitePages(object $test, bool $entryPages = true, array $devnullUsers = []): array
{
    $site = new Site(Entries::load($test->dir, $test->today), $test->today, $devnullUsers, entryPages: $entryPages);

    return array_map(
        fn (string $html) => HTMLDocument::createFromString($html, LIBXML_NOERROR),
        $site->pages(),
    );
}

function entryPage(object $test, string $slug, array $devnullUsers = []): HTMLDocument
{
    return sitePages($test, devnullUsers: $devnullUsers)["entries/{$slug}/index.html"];
}

it('turns the entry pages on', function () {
    expect(Site::ENTRY_PAGES)->toBeTrue();
});

it('builds a page for each entry, current and archived', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    writeEntry($this->dir, '2026-03-01-old.php');

    expect(array_keys(sitePages($this)))->toContain('entries/pest-5/index.html', 'entries/old/index.html')
        ->and(array_keys(sitePages($this, entryPages: false)))->not->toContain('entries/pest-5/index.html');
});

it('gives each entry page its own title, description and address', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $doc = entryPage($this, 'pest-5');
    $meta = fn (string $selector) => $doc->querySelector($selector)?->getAttribute('content');

    expect($doc->querySelector('title')->textContent)->toBe('Pest 5: shipped with PHP | phplives.dev')
        ->and($meta('meta[property="og:title"]'))->toBe('Pest 5: shipped with PHP')
        ->and($meta('meta[name="description"]'))->toBe('A testing framework for PHP, focused on simplicity.')
        ->and($meta('meta[property="og:description"]'))->toBe('A testing framework for PHP, focused on simplicity.')
        ->and($doc->querySelector('link[rel="canonical"]')->getAttribute('href'))->toBe('https://phplives.dev/entries/pest-5/')
        ->and($meta('meta[property="og:url"]'))->toBe('https://phplives.dev/entries/pest-5/')
        ->and($meta('meta[property="og:image"]'))->toBe('https://phplives.dev/share.png?v=3')
        ->and($doc->querySelector('meta[name="robots"]'))->toBeNull()
        ->and(hrefs($doc, '.nav a[aria-current]'))->toBe([]);
});

it('points to the Markdown copy of the entry', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $link = entryPage($this, 'pest-5')->querySelector('link[rel="alternate"][type="text/markdown"]');

    expect($link?->getAttribute('href'))->toBe('/entries/pest-5.md');
});

it('shows the full label, with the name as the page heading', function () {
    writeEntry($this->dir, '2026-08-01-first.php', ['name' => 'First']);
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['stack' => 'laravel']);
    $doc = entryPage($this, 'pest-5');
    $home = sitePages($this)['index.html'];

    expect(texts($doc, 'h1'))->toBe(['Pest 5'])
        ->and($doc->querySelectorAll('.depot .lab'))->toHaveCount(1)
        ->and(texts($doc, '.depot .lab .cap'))->toBe(['From', 'Contents', 'Shipped', 'Track'])
        ->and(texts($doc, '.depot .lab .contents p'))->toBe(['A testing framework for PHP, focused on simplicity.'])
        ->and(texts($doc, '.depot .lab .route b'))->toBe(['LRV'])
        ->and(texts($doc, '.depot .lab .tid'))->toBe(texts($home, '#manifest .lab:first-child .tid'))
        ->and($doc->querySelector('.depot .lab .pm'))->not->toBeNull()
        ->and($doc->querySelector('.depot .lab .bars'))->not->toBeNull()
        ->and($doc->querySelector('.depot .lab .more'))->toBeNull();
});

it('marks the label as the newest parcel only on the newest entry\'s page', function () {
    writeEntry($this->dir, '2026-08-01-first.php');
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(entryPage($this, 'pest-5')->querySelectorAll('.lab.newest .lab-new'))->toHaveCount(1)
        ->and(entryPage($this, 'first')->querySelector('.lab.newest'))->toBeNull()
        ->and(entryPage($this, 'first')->querySelector('.lab-new'))->toBeNull();
});

it('links back to the manifest, or to the archive for an archived entry', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    writeEntry($this->dir, '2026-03-01-old.php');

    expect(hrefs(entryPage($this, 'pest-5'), '.back a'))->toBe(['/'])
        ->and(texts(entryPage($this, 'pest-5'), '.back a'))->toBe(['Back to the manifest'])
        ->and(hrefs(entryPage($this, 'old'), '.back a'))->toBe(['/archive/'])
        ->and(texts(entryPage($this, 'old'), '.back a'))->toBe(['Back to the archive']);
});

it('links to the parcels shipped just before and just after, over all entries', function () {
    writeEntry($this->dir, '2026-03-01-old.php', ['name' => 'Old']);
    writeEntry($this->dir, '2026-08-01-middle.php', ['name' => 'Middle', 'stack' => 'symfony']);
    writeEntry($this->dir, '2026-09-01-new.php', ['name' => 'New']);

    $middle = entryPage($this, 'middle');
    $old = entryPage($this, 'old');
    $new = entryPage($this, 'new');

    expect(hrefs($middle, '.nbr a[rel="prev"]'))->toBe(['/entries/old/'])
        ->and(hrefs($middle, '.nbr a[rel="next"]'))->toBe(['/entries/new/'])
        ->and(texts($middle, '.nbr a[rel="prev"] .stub-cap'))->toBe(['Shipped before'])
        ->and(texts($middle, '.nbr a[rel="next"] .stub-cap'))->toBe(['Shipped after'])
        ->and(texts($middle, '.nbr a[rel="prev"] .stub-name'))->toBe(['Old'])
        ->and(texts($middle, '.nbr a[rel="prev"] .stub-date'))->toBe(['01/03/2026'])
        ->and(texts($old, '.nbr a[rel="next"] .stub-code'))->toBe(['SYM'])
        ->and(hrefs($old, '.nbr a'))->toBe(['/entries/middle/'])
        ->and($old->querySelector('.nbr a[rel="prev"]'))->toBeNull()
        ->and(hrefs($new, '.nbr a'))->toBe(['/entries/middle/'])
        ->and($new->querySelector('.nbr a[rel="next"]'))->toBeNull();
});

it('has no neighbour row when there is only one entry', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(entryPage($this, 'pest-5')->querySelector('.nbr'))->toBeNull();
});

it('lists the facts from the entry file on the docket', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'makers' => [
            ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
            ['name' => 'coolLabs'],
        ],
        'checked' => '2026-10-18',
        'added' => '2026-10-19',
    ]);
    $doc = entryPage($this, 'pest-5', ['nunomaduro']);
    $times = array_map(
        fn ($node) => [$node->getAttribute('datetime'), $node->textContent],
        iterator_to_array($doc->querySelectorAll('.docket dd time')),
    );

    expect(texts($doc, '.docket dt'))->toBe(['Made by', 'Source', 'Shipped', 'Date from', 'Added', 'Checked'])
        ->and(hrefs($doc, '.docket .dk-makers a'))->toBe([
            'https://github.com/nunomaduro',
            'https://dev-null.sh/@nunomaduro',
        ])
        ->and(texts($doc, '.docket .dk-makers .who'))->toBe(['Nuno Maduro', 'coolLabs'])
        ->and(hrefs($doc, '.docket .dk-link'))->toBe([
            'https://github.com/pestphp/pest',
            'https://github.com/pestphp/pest/releases/tag/v5.0.0',
        ])
        ->and($times)->toBe([
            ['2026-09-01', '01/09/2026'],
            ['2026-10-19', '19/10/2026'],
            ['2026-10-18', '18/10/2026'],
        ])
        ->and($doc->querySelector('.docket .dk-details'))->toBeNull()
        ->and($doc->querySelector('.docket .dk-install'))->toBeNull();
});

it('shows the optional facts only when the entry gives them', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'details' => 'Pest 5 adds browser testing. It needs PHP 8.4.',
        'package' => 'pestphp/pest',
        'licence' => 'MIT',
        'docs' => 'https://pestphp.com/docs',
    ]);
    $doc = entryPage($this, 'pest-5');
    $button = $doc->querySelector('.docket .dk-install button[data-copy]');

    expect(texts($doc, '.docket .dk-details'))->toBe(['Pest 5 adds browser testing. It needs PHP 8.4.'])
        ->and(texts($doc, '.docket .dk-install code'))->toBe(['$ composer require pestphp/pest'])
        ->and($button->getAttribute('data-copy'))->toBe('composer require pestphp/pest')
        ->and($button->hasAttribute('hidden'))->toBeTrue()
        ->and(texts($doc, '.docket dt'))->toBe(['Made by', 'Source', 'Docs', 'Licence', 'Shipped', 'Date from', 'Added', 'Checked'])
        ->and(hrefs($doc, '.docket .dk-link'))->toContain('https://pestphp.com/docs')
        ->and(texts($doc, '.docket .dk-licence'))->toBe(['MIT']);
});

it('lets the visitor copy the link to the page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $doc = entryPage($this, 'pest-5');
    $button = $doc->querySelector('.dk-share button[data-copy]');

    expect($button->getAttribute('data-copy'))->toBe('https://phplives.dev/entries/pest-5/')
        ->and($button->textContent)->toBe('Copy link')
        ->and($button->getAttribute('data-copy-fail'))->toBe('Could not copy. Select the link and copy it.')
        ->and($button->hasAttribute('hidden'))->toBeTrue()
        ->and(texts($doc, '.dk-share .dk-url'))->toBe(['phplives.dev/entries/pest-5/'])
        ->and(texts($doc, 'body > .pl > script'))->toHaveCount(2);
});

it('offers the device share menu where the browser has one', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $button = entryPage($this, 'pest-5')->querySelector('.dk-share button[data-share]');

    expect($button->getAttribute('data-share'))->toBe('https://phplives.dev/entries/pest-5/')
        ->and($button->getAttribute('data-share-title'))->toBe('Pest 5: shipped with PHP')
        ->and($button->textContent)->toBe('Share')
        ->and($button->hasAttribute('hidden'))->toBeTrue();
});

it('escapes entry text on the entry page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['name' => '<b>Pest</b>', 'details' => 'A <script> tag.']);
    $html = (new Site(Entries::load($this->dir, $this->today), $this->today))->pages()['entries/pest-5/index.html'];

    expect($html)->toContain('&lt;b&gt;Pest&lt;/b&gt;')->not->toContain('<b>Pest</b>')
        ->and($html)->toContain('A &lt;script&gt; tag.');
});

it('keeps the site rules on the entry page', function () {
    writeEntry($this->dir, '2026-08-01-first.php');
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['docs' => 'https://pestphp.com/docs']);
    $doc = entryPage($this, 'pest-5', ['nunomaduro']);
    $links = iterator_to_array($doc->querySelectorAll('a[href]'));

    expect($doc->saveHtml())->not->toContain("\u{2014}");

    foreach ($links as $a) {
        $href = $a->getAttribute('href');

        if (str_starts_with($href, '/') || str_starts_with($href, '#')) {
            expect($a->hasAttribute('target'))->toBeFalse($href);
        } else {
            expect($href)->toStartWith('https://')
                ->and($a->getAttribute('target'))->toBe('_blank', $href);
        }
    }
});

it('links each label and each list row to its entry page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['name' => 'Pest 5']);
    writeEntry($this->dir, '2026-03-01-old.php', ['name' => 'Old']);
    $pages = sitePages($this);

    expect(hrefs($pages['index.html'], '#manifest .lab .more'))->toBe(['/entries/pest-5/'])
        ->and(texts($pages['index.html'], '#manifest .lab .more'))->toBe(['Details for Pest 5'])
        ->and(hrefs($pages['index.html'], '#manifest table .more'))->toBe(['/entries/pest-5/'])
        ->and(hrefs($pages['archive/index.html'], 'table .more'))->toBe(['/entries/old/']);
});

it('has no Details links while the entry pages are off', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    writeEntry($this->dir, '2026-03-01-old.php');
    $pages = sitePages($this, entryPages: false);

    expect($pages['index.html']->querySelector('.more'))->toBeNull()
        ->and($pages['archive/index.html']->querySelector('.more'))->toBeNull();
});
