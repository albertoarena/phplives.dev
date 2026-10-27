<?php

declare(strict_types=1);

use Dom\HTMLDocument;
use PhpLives\Entries;
use PhpLives\QrCode;
use PhpLives\ShareCard;
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
function sitePages(object $test, bool $entryPages = true, array $devnullUsers = [], bool $shareCards = true): array
{
    $site = new Site(Entries::load($test->dir, $test->today), $test->today, $devnullUsers, entryPages: $entryPages, shareCards: $shareCards);

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
        ->and(texts($doc, '.depot .lab .cap'))->toBe(['From', 'Contents', 'Shipped', 'Track', 'Scan'])
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
        ->and(texts($middle, '.nbr a[rel="prev"] .nstub-cap'))->toBe(['Shipped before'])
        ->and(texts($middle, '.nbr a[rel="next"] .nstub-cap'))->toBe(['Shipped after'])
        ->and(texts($middle, '.nbr a[rel="prev"] .nstub-name'))->toBe(['Old'])
        ->and(texts($middle, '.nbr a[rel="prev"] .nstub-date'))->toBe(['01/03/2026'])
        ->and(texts($old, '.nbr a[rel="next"] .nstub-code'))->toBe(['SYM'])
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

it('tags the topics on the docket, each linked to the main page filtered by it', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['topics' => ['testing', 'mcp'], 'licence' => 'MIT']);
    writeEntry($this->dir, '2026-08-01-tia.php', ['topics' => ['testing']]);
    $doc = entryPage($this, 'pest-5');

    expect(texts($doc, '.docket dt'))->toBe(['Made by', 'Source', 'Licence', 'Topics', 'Shipped', 'Date from', 'Added', 'Checked'])
        ->and(texts($doc, '.docket .dk-topics li'))->toBe(['Testing', 'MCP'])
        ->and(hrefs($doc, '.docket .dk-topics a'))->toBe(['/#topic=testing'])
        ->and(texts($doc, '.docket .dk-topics span.dk-topic'))->toBe(['MCP'])
        ->and($doc->querySelector('.docket .dk-topics a')->getAttribute('aria-label'))->toBe('More entries about Testing');
});

it('has no Topics row when the entry has no topics', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(entryPage($this, 'pest-5')->querySelector('.docket .dk-topics'))->toBeNull();
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

it('offers the maker the Markdown for a README badge, under the share foot', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $doc = entryPage($this, 'pest-5');
    $snippet = '[![Featured on phplives.dev](https://phplives.dev/badge.svg)](https://phplives.dev/entries/pest-5/)';
    $button = $doc->querySelector('.docket .dk-badge button[data-copy]');

    expect($doc->querySelector('.docket .dk-share + .dk-badge'))->not->toBeNull()
        ->and(texts($doc, '.dk-badge .cap'))->toBe(['Badge for your README'])
        ->and(texts($doc, '.dk-badge p'))->toBe(['Listed here? Add this badge to your README.'])
        ->and(texts($doc, '.dk-badge .cli-line code'))->toBe([$snippet])
        ->and($button->getAttribute('data-copy'))->toBe($snippet)
        ->and($button->getAttribute('data-copy-status'))->toBe('badge-status')
        ->and($button->getAttribute('data-copy-fail'))->toBe('Could not copy. Select the Markdown and copy it.')
        ->and($button->textContent)->toBe('Copy')
        ->and($button->hasAttribute('hidden'))->toBeTrue()
        ->and($doc->getElementById('badge-status')?->getAttribute('role'))->toBe('status')
        ->and($doc->querySelector('.dk-badge img')->getAttribute('src'))->toBe('/badge.svg')
        ->and($doc->querySelector('.dk-badge img')->getAttribute('alt'))->toBe('Featured on phplives.dev badge');
});

it('puts a QR code of the page on the label, beside the barcode', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $doc = entryPage($this, 'pest-5');
    $entry = Entries::load($this->dir, $this->today)->all()[0];
    $code = $doc->querySelector('.lab .qr-blk .qr-cell svg.qr-code');

    expect($doc->querySelector('.lab .qr-blk .qr-main .bars'))->not->toBeNull()
        ->and($doc->querySelector('.lab .qr-blk .qr-main .track'))->not->toBeNull()
        ->and(texts($doc, '.lab .qr-cell .cap'))->toBe(['Scan'])
        ->and($code->getAttribute('role'))->toBe('img')
        ->and($code->getAttribute('aria-label'))->toBe('QR code that opens this page')
        ->and($code->querySelector('path')->getAttribute('d'))->toBe(QrCode::forEntry($entry)->path(3));
});

it('keeps the QR code off the labels on the main page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(sitePages($this)['index.html']->querySelector('.qr-code'))->toBeNull();
});

it('offers the QR code as SVG and PNG files, under the badge', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $doc = (new Site(Entries::load($this->dir, $this->today), $this->today, qrPng: true))->pages()['entries/pest-5/index.html'];
    $doc = HTMLDocument::createFromString($doc, LIBXML_NOERROR);
    $links = iterator_to_array($doc->querySelectorAll('.dk-qr .dk-qr-actions a'));

    expect($doc->querySelector('.docket .dk-badge + .dk-qr'))->not->toBeNull()
        ->and(texts($doc, '.dk-qr .cap'))->toBe(['QR code for this page'])
        ->and(texts($doc, '.dk-qr p'))->toBe(['Put it on a slide, a poster or a flyer. A phone camera opens this page.'])
        ->and(array_map(fn ($a) => $a->getAttribute('href'), $links))->toBe(['/entries/pest-5/qr.svg', '/entries/pest-5/qr.png'])
        ->and(array_map(fn ($a) => $a->getAttribute('download'), $links))->toBe(['phplives-pest-5-qr.svg', 'phplives-pest-5-qr.png'])
        ->and(texts($doc, '.dk-qr .dk-qr-actions a'))->toBe(['Download SVG of the QR code', 'Download PNG of the QR code'])
        // The label above shows the same code to screen readers, so the preview is hidden from them.
        ->and($doc->querySelector('.dk-qr svg.qr-code')->getAttribute('aria-hidden'))->toBe('true');
});

it('offers only the SVG file when GD cannot write the PNG file', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $html = (new Site(Entries::load($this->dir, $this->today), $this->today, qrPng: false))->pages()['entries/pest-5/index.html'];
    $doc = HTMLDocument::createFromString($html, LIBXML_NOERROR);

    expect(texts($doc, '.dk-qr .dk-qr-actions a'))->toBe(['Download SVG of the QR code']);
});

it('writes the SVG file of the QR code next to each entry page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $entries = Entries::load($this->dir, $this->today);

    expect((new Site($entries, $this->today))->files()['entries/pest-5/qr.svg'])->toBe(QrCode::svg($entries->all()[0]))
        ->and((new Site($entries, $this->today, entryPages: false))->files())->not->toHaveKey('entries/pest-5/qr.svg');
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

/**
 * The content of each share image tag on a page.
 *
 * @return array<string, string|null>
 */
function imageMeta(HTMLDocument $doc): array
{
    $meta = fn (string $selector) => $doc->querySelector($selector)?->getAttribute('content');

    return [
        'image' => $meta('meta[property="og:image"]'),
        'width' => $meta('meta[property="og:image:width"]'),
        'height' => $meta('meta[property="og:image:height"]'),
        'alt' => $meta('meta[property="og:image:alt"]'),
        'card' => $meta('meta[name="twitter:card"]'),
    ];
}

it('shares each entry page with its own card', function () {
    writeEntry($this->dir, '2026-09-14-laravel-vet.php', ['name' => 'Laravel Vet', 'makers' => [['name' => 'Laravel', 'github' => 'laravel']]]);
    writeEntry($this->dir, '2026-03-01-old.php', ['name' => 'Old']);
    $pages = sitePages($this);
    $entry = Entries::load($this->dir, $this->today)->all()[0];

    expect(imageMeta($pages['entries/laravel-vet/index.html']))->toBe([
        'image' => ShareCard::url($entry),
        'width' => '1200',
        'height' => '630',
        'alt' => 'Shipping label for Laravel Vet by Laravel, shipped on 14/09/2026. phplives.dev',
        'card' => 'summary_large_image',
    ])
        ->and(imageMeta($pages['entries/old/index.html'])['image'])->toStartWith('https://phplives.dev/entries/old/card.png?v=');
});

it('shares the other pages with the site card', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    foreach (['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html'] as $path) {
        expect(imageMeta(sitePages($this)[$path]))->toBe([
            'image' => 'https://phplives.dev/share.png?v=3',
            'width' => '1200',
            'height' => '630',
            'alt' => 'PHP delivers. PHP lives! Real things people shipped with PHP in the last 6 months. phplives.dev',
            'card' => 'summary_large_image',
        ]);
    }
});

it('shares entry pages with the site card when there are no cards', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $meta = imageMeta(sitePages($this, shareCards: false)['entries/pest-5/index.html']);

    expect($meta['image'])->toBe('https://phplives.dev/share.png?v=3')
        ->and($meta['alt'])->toStartWith('PHP delivers. PHP lives!');
});

it('draws cards only where GD can and only with entry pages', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $entries = Entries::load($this->dir, $this->today);

    expect((new Site($entries, $this->today, entryPages: false))->hasShareCards())->toBeFalse()
        ->and((new Site($entries, $this->today, shareCards: false))->hasShareCards())->toBeFalse()
        ->and((new Site($entries, $this->today))->hasShareCards())->toBe(ShareCard::available());
});
