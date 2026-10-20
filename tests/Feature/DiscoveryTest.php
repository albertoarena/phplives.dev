<?php

declare(strict_types=1);

use Dom\HTMLDocument;
use PhpLives\Entries;
use PhpLives\Site;

beforeEach(function () {
    $this->dir = sys_get_temp_dir().'/phplives-discovery-'.bin2hex(random_bytes(4));
    mkdir($this->dir);
    $this->today = new DateTimeImmutable('2026-10-20');
});

afterEach(function () {
    array_map(unlink(...), glob($this->dir.'/*'));
    rmdir($this->dir);
});

/**
 * Every file the site writes, keyed by its path in the output folder.
 *
 * @return array<string, string>
 */
function files(object $test, bool $entryPages = false): array
{
    return (new Site(Entries::load($test->dir, $test->today), $test->today, entryPages: $entryPages))->files();
}

function xml(string $text): SimpleXMLElement
{
    $xml = simplexml_load_string($text);

    expect($xml)->toBeInstanceOf(SimpleXMLElement::class);

    return $xml;
}

/**
 * @return list<string> The text of each element at an XPath, in the Atom namespace as "a".
 */
function atom(string $text, string $path): array
{
    $xml = xml($text);
    $xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');

    return array_map(fn ($node) => (string) $node, $xml->xpath($path));
}

it('writes the HTML pages and the files that help people and tools find them', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['stack' => 'laravel']);

    expect(array_keys(files($this)))->toBe([
        'index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', '404.html',
        'robots.txt', 'sitemap.xml', 'llms.txt', 'feed.xml', 'feed/laravel.xml', 'entries/pest-5.md', '_headers',
    ]);
});

it('lets every crawler in, points to the sitemap and sets the content signals', function () {
    expect(files($this)['robots.txt'])->toBe(<<<'TXT'
        User-agent: *
        Content-Signal: search=yes, ai-input=yes, ai-train=yes
        Allow: /

        Sitemap: https://phplives.dev/sitemap.xml

        TXT);
});

it('lists every real page in the sitemap, and not the 404 page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    $sitemap = xml(files($this)['sitemap.xml']);

    expect($sitemap->getName())->toBe('urlset')
        ->and($sitemap->getNamespaces())->toBe(['' => 'http://www.sitemaps.org/schemas/sitemap/0.9'])
        ->and(array_map(fn ($url) => (string) $url->loc, iterator_to_array($sitemap->url, false)))->toBe([
            'https://phplives.dev/',
            'https://phplives.dev/archive/',
            'https://phplives.dev/privacy/',
            'https://phplives.dev/credits/',
        ]);
});

it('dates the list pages by the newest change to any entry', function () {
    writeEntry($this->dir, '2026-09-01-a.php', ['checked' => '2026-10-18', 'added' => '2026-10-17']);
    writeEntry($this->dir, '2026-03-01-b.php', ['checked' => '2026-10-16', 'added' => '2026-10-19']);

    $urls = iterator_to_array(xml(files($this)['sitemap.xml'])->url, false);
    $lastmod = array_combine(
        array_map(fn ($url) => (string) $url->loc, $urls),
        array_map(fn ($url) => isset($url->lastmod) ? (string) $url->lastmod : null, $urls),
    );

    expect($lastmod)->toBe([
        'https://phplives.dev/' => '2026-10-19',
        'https://phplives.dev/archive/' => '2026-10-19',
        'https://phplives.dev/privacy/' => null,
        'https://phplives.dev/credits/' => null,
    ]);
});

it('leaves out the list page dates when there are no entries', function () {
    expect(files($this)['sitemap.xml'])->not->toContain('<lastmod>');
});

it('keeps the entry pages out of the sitemap when they are off', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(files($this)['sitemap.xml'])->not->toContain('/entries/');
});

it('lists the entry pages in the sitemap of the real site', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect((new Site(Entries::load($this->dir, $this->today), $this->today))->files()['sitemap.xml'])
        ->toContain('<loc>https://phplives.dev/entries/pest-5/</loc>');
});

it('lists each entry page in the sitemap, dated by its check, once entry pages are on', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['checked' => '2026-10-18']);
    writeEntry($this->dir, '2026-03-01-old.php', ['checked' => '2026-10-17']);

    $urls = array_slice(iterator_to_array(xml(files($this, entryPages: true)['sitemap.xml'])->url, false), 4);

    expect(array_map(fn ($url) => [(string) $url->loc, (string) $url->lastmod], $urls))->toBe([
        ['https://phplives.dev/entries/pest-5/', '2026-10-18'],
        ['https://phplives.dev/entries/old/', '2026-10-17'],
    ]);
});

it('describes the site for language models in llms.txt', function () {
    writeEntry($this->dir, '2026-09-01-older.php', ['name' => 'Older', 'sentence' => 'The older one.']);
    writeEntry($this->dir, '2026-10-01-newer.php', ['name' => 'Newer', 'sentence' => 'The newer one.']);
    writeEntry($this->dir, '2026-03-01-old.php', ['name' => 'Old', 'sentence' => 'An old one.']);

    $llms = files($this)['llms.txt'];

    expect($llms)->toStartWith("# phplives.dev\n\n> ")
        ->and($llms)->toContain('real things people shipped with PHP in the last 6 months')
        ->and($llms)->toContain('PHP delivers. PHP lives!')
        ->and($llms)->toContain('PHP is the main language on its server side.')
        ->and($llms)->toContain(<<<'MD'
            ## Entries

            - [Newer](https://phplives.dev/entries/newer.md): The newer one.
            - [Older](https://phplives.dev/entries/older.md): The older one.

            ## Archive

            - [Old](https://phplives.dev/entries/old.md): An old one.

            ## Optional

            - [Source code](https://github.com/albertoarena/phplives.dev)
            MD)
        ->and($llms)->toContain('(https://github.com/albertoarena/phplives.dev/blob/main/CONTRIBUTING.md)');
});

it('leaves the archive out of llms.txt when it is empty', function () {
    writeEntry($this->dir, '2026-10-01-newer.php');

    expect(files($this)['llms.txt'])->not->toContain('## Archive');
});

it('never quotes the number of entries in llms.txt', function () {
    foreach (['2026-10-01-a', '2026-09-01-b', '2026-08-01-c', '2026-07-01-d', '2026-06-01-e'] as $file) {
        writeEntry($this->dir, "{$file}.php", ['name' => 'Name', 'sentence' => 'A thing.']);
    }

    $text = preg_replace('#\(https://[^)]*\)#', '', files($this)['llms.txt']);

    expect($text)->not->toMatch('/\b5\b|\bfive\b/i');
});

it('writes a Markdown copy of each entry', function () {
    writeEntry($this->dir, '2026-09-14-laravel-vet.php', [
        'name' => 'Laravel Vet',
        'sentence' => 'A dependency audit for PHP.',
        'makers' => [['name' => 'Laravel', 'github' => 'laravel'], ['name' => 'coolLabs']],
        'proof' => ['kind' => 'tag', 'url' => 'https://github.com/laravel/vet/releases/tag/v0.1.0'],
        'source' => 'https://github.com/laravel/vet',
        'stack' => 'laravel',
        'checked' => '2026-10-18',
        'added' => '2026-10-19',
    ]);

    expect(files($this)['entries/laravel-vet.md'])->toBe(<<<'MD'
        # Laravel Vet

        A dependency audit for PHP.

        - Made by: [Laravel](https://github.com/laravel), coolLabs
        - Shipped: 14/09/2026
        - Date from: [version tag](https://github.com/laravel/vet/releases/tag/v0.1.0)
        - Source: [github.com/laravel/vet](https://github.com/laravel/vet)
        - Stack: Laravel
        - Checked: 18/10/2026
        - Added: 19/10/2026

        See more at [phplives.dev](https://phplives.dev)

        MD);
});

it('adds the optional facts to the Markdown copy when an entry gives them', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'details' => 'Pest 5 adds browser testing. It needs PHP 8.4.',
        'package' => 'pestphp/pest',
        'licence' => 'MIT',
        'docs' => 'https://pestphp.com/docs',
        'checked' => '2026-10-18',
        'added' => '2026-10-19',
    ]);

    expect(files($this)['entries/pest-5.md'])->toBe(<<<'MD'
        # Pest 5

        A testing framework for PHP, focused on simplicity.

        Pest 5 adds browser testing. It needs PHP 8.4.

        - Made by: [Nuno Maduro](https://github.com/nunomaduro)
        - Shipped: 01/09/2026
        - Date from: [release](https://github.com/pestphp/pest/releases/tag/v5.0.0)
        - Source: [github.com/pestphp/pest](https://github.com/pestphp/pest)
        - Docs: [pestphp.com/docs](https://pestphp.com/docs)
        - Install: `composer require pestphp/pest`
        - Licence: MIT
        - Stack: plain PHP
        - Checked: 18/10/2026
        - Added: 19/10/2026

        See more at [phplives.dev](https://phplives.dev)

        MD);
});

it('escapes Markdown in entry text', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'name' => 'Pest [5] *beta*',
        'sentence' => 'Tests <b>fast</b> with a_b and `code`.',
        'makers' => [['name' => 'Nuno [Maduro]', 'github' => 'nunomaduro']],
    ]);

    $files = files($this);

    expect($files['entries/pest-5.md'])
        ->toStartWith("# Pest \\[5\\] \\*beta\\*\n\nTests \\<b\\>fast\\</b\\> with a\\_b and \\`code\\`.\n")
        ->toContain('- Made by: [Nuno \\[Maduro\\]](https://github.com/nunomaduro)')
        ->and($files['llms.txt'])->toContain('- [Pest \\[5\\] \\*beta\\*](https://phplives.dev/entries/pest-5.md): Tests \\<b\\>fast');
});

it('builds an Atom feed of the newest entries', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'name' => 'Pest 5',
        'sentence' => 'A testing framework.',
        'makers' => [['name' => 'Nuno Maduro', 'github' => 'nunomaduro'], ['name' => 'coolLabs']],
        'stack' => 'laravel',
        'added' => '2026-10-19',
    ]);
    writeEntry($this->dir, '2026-03-01-old.php', ['added' => '2026-10-17']);

    $feed = files($this)['feed.xml'];

    expect(xml($feed)->getName())->toBe('feed')
        ->and(xml($feed)->getNamespaces())->toBe(['' => 'http://www.w3.org/2005/Atom'])
        ->and(atom($feed, '/a:feed/a:title'))->toBe(['phplives.dev'])
        ->and(atom($feed, '/a:feed/a:subtitle'))->toBe(['PHP delivers. PHP lives!'])
        ->and(atom($feed, '/a:feed/a:id'))->toBe(['tag:phplives.dev,2026:feed'])
        ->and(atom($feed, '/a:feed/a:updated'))->toBe(['2026-10-19T00:00:00Z'])
        ->and(atom($feed, '/a:feed/a:link[@rel="self"]/@href'))->toBe(['https://phplives.dev/feed.xml'])
        ->and(atom($feed, '/a:feed/a:link[@rel="self"]/@type'))->toBe(['application/atom+xml'])
        ->and(atom($feed, '/a:feed/a:link[@rel="alternate"]/@href'))->toBe(['https://phplives.dev/'])
        ->and(atom($feed, '/a:feed/a:entry/a:id'))->toBe(['tag:phplives.dev,2026:pest-5', 'tag:phplives.dev,2026:old'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:title'))->toBe(['Pest 5'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:published'))->toBe(['2026-10-19T00:00:00Z'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:updated'))->toBe(['2026-10-19T00:00:00Z'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:link[@rel="alternate"]/@href'))->toBe(['https://github.com/pestphp/pest'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:summary'))->toBe(['A testing framework. Shipped 01/09/2026. Stack: Laravel.'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:author/a:name'))->toBe(['Nuno Maduro', 'coolLabs'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:author/a:uri'))->toBe(['https://github.com/nunomaduro'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:category/@term'))->toBe(['laravel'])
        ->and(atom($feed, '/a:feed/a:entry[1]/a:category/@label'))->toBe(['Laravel']);
});

it('orders the feed by the date added, then by the date shipped', function () {
    writeEntry($this->dir, '2026-10-01-new-ship-old-add.php', ['added' => '2026-10-17']);
    writeEntry($this->dir, '2026-08-01-old-ship-new-add.php', ['added' => '2026-10-19']);
    writeEntry($this->dir, '2026-09-01-same-day-later-ship.php', ['added' => '2026-10-19']);

    expect(atom(files($this)['feed.xml'], '/a:feed/a:entry/a:id'))->toBe([
        'tag:phplives.dev,2026:same-day-later-ship',
        'tag:phplives.dev,2026:old-ship-new-add',
        'tag:phplives.dev,2026:new-ship-old-add',
    ]);
});

it('keeps only the newest 50 entries in the feed', function () {
    $start = new DateTimeImmutable('2026-05-01');

    for ($day = 0; $day < 55; $day++) {
        $date = $start->modify("+{$day} days")->format('Y-m-d');
        writeEntry($this->dir, "{$date}-entry-{$day}.php", ['added' => $date]);
    }

    $ids = atom(files($this)['feed.xml'], '/a:feed/a:entry/a:id');

    expect($ids)->toHaveCount(50)
        ->and($ids[0])->toBe('tag:phplives.dev,2026:entry-54')
        ->and($ids[49])->toBe('tag:phplives.dev,2026:entry-5');
});

it('links each feed entry to its entry page once entry pages are on', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(atom(files($this, entryPages: true)['feed.xml'], '/a:feed/a:entry/a:link[@rel="alternate"]/@href'))
        ->toBe(['https://phplives.dev/entries/pest-5/']);
});

it('escapes entry text in the feed', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'name' => 'Pest <5> & "more"',
        'sentence' => 'Fast & <small>.',
        'makers' => [['name' => 'A & B']],
    ]);

    $feed = files($this)['feed.xml'];

    expect(atom($feed, '/a:feed/a:entry/a:title'))->toBe(['Pest <5> & "more"'])
        ->and(atom($feed, '/a:feed/a:entry/a:summary'))->toBe(['Fast & <small>. Shipped 01/09/2026. Stack: plain PHP.'])
        ->and(atom($feed, '/a:feed/a:entry/a:author/a:name'))->toBe(['A & B']);
});

it('dates an empty feed by the build day', function () {
    $feed = files($this)['feed.xml'];

    expect(atom($feed, '/a:feed/a:updated'))->toBe(['2026-10-20T00:00:00Z'])
        ->and(atom($feed, '/a:feed/a:entry'))->toBe([]);
});

it('builds one feed per stack that has entries', function () {
    writeEntry($this->dir, '2026-09-01-a.php', ['stack' => 'laravel', 'added' => '2026-10-18']);
    writeEntry($this->dir, '2026-08-01-b.php', ['stack' => 'laravel', 'added' => '2026-10-17']);
    writeEntry($this->dir, '2026-07-01-c.php', ['stack' => 'php', 'added' => '2026-10-19']);

    $files = files($this);
    $laravel = $files['feed/laravel.xml'];

    expect(array_keys(array_filter($files, fn ($path) => str_starts_with($path, 'feed/'), ARRAY_FILTER_USE_KEY)))
        ->toBe(['feed/laravel.xml', 'feed/php.xml'])
        ->and(atom($laravel, '/a:feed/a:title'))->toBe(['phplives.dev: Laravel'])
        ->and(atom($laravel, '/a:feed/a:id'))->toBe(['tag:phplives.dev,2026:feed/laravel'])
        ->and(atom($laravel, '/a:feed/a:updated'))->toBe(['2026-10-18T00:00:00Z'])
        ->and(atom($laravel, '/a:feed/a:link[@rel="self"]/@href'))->toBe(['https://phplives.dev/feed/laravel.xml'])
        ->and(atom($laravel, '/a:feed/a:entry/a:id'))->toBe(['tag:phplives.dev,2026:a', 'tag:phplives.dev,2026:b'])
        ->and(atom($files['feed.xml'], '/a:feed/a:entry/a:id'))->toHaveCount(3);
});

it('tells feed readers where the feed is, on every page', function (string $page) {
    $doc = HTMLDocument::createFromString(files($this)[$page], LIBXML_NOERROR);
    $link = $doc->querySelector('link[rel="alternate"][type="application/atom+xml"]');

    expect($link?->getAttribute('href'))->toBe('/feed.xml')
        ->and($link->getAttribute('title'))->toBe('phplives.dev');
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', '404.html']);

it('serves the feeds as Atom', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(files($this)['_headers'])->toBe(<<<'TXT'
        /feed.xml
          Content-Type: application/atom+xml; charset=utf-8
        /feed/*
          Content-Type: application/atom+xml; charset=utf-8

        TXT);
});
