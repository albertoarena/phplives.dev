<?php

declare(strict_types=1);

use Dom\HTMLDocument;
use PhpLives\Entries;
use PhpLives\Post;
use PhpLives\Site;

beforeEach(function () {
    $this->dir = sys_get_temp_dir().'/phplives-site-'.bin2hex(random_bytes(4));
    mkdir($this->dir);
    $this->today = new DateTimeImmutable('2026-10-16');
});

afterEach(function () {
    array_map(unlink(...), glob($this->dir.'/*'));
    rmdir($this->dir);
});

function pages(object $test, array $devnullUsers = [], array $posts = [], bool $entryPages = false): array
{
    $site = new Site(Entries::load($test->dir, $test->today), $test->today, $devnullUsers, $posts, $entryPages);

    return array_map(
        fn (string $html) => HTMLDocument::createFromString($html, LIBXML_NOERROR),
        $site->pages(),
    );
}

function texts(HTMLDocument $doc, string $selector): array
{
    return array_map(
        fn ($node) => trim(preg_replace('/\s+/', ' ', $node->textContent)),
        iterator_to_array($doc->querySelectorAll($selector)),
    );
}

function hrefs(HTMLDocument $doc, string $selector): array
{
    return array_map(fn ($node) => $node->getAttribute('href'), iterator_to_array($doc->querySelectorAll($selector)));
}

it('builds the main page, the archive, the privacy page and the credits', function () {
    expect(array_keys(pages($this)))->toBe(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html', '404.html']);
});

it('quotes the line exactly at the top of the main page', function () {
    expect(texts(pages($this)['index.html'], 'h1'))->toBe(['PHP delivers. PHP lives!']);
});

it('puts the line in the title of the main page', function () {
    expect(pages($this)['index.html']->querySelector('title')->textContent)
        ->toBe('phplives.dev: PHP delivers. PHP lives!');
});

it('puts the entries added last on full labels, and marks only the newest', function () {
    writeEntry($this->dir, '2026-08-01-older.php', ['name' => 'Older', 'added' => '2026-10-13']);
    writeEntry($this->dir, '2026-10-01-newer.php', ['name' => 'Newer', 'added' => '2026-10-14']);
    writeEntry($this->dir, '2026-09-01-before.php', ['name' => 'Before', 'added' => '2026-10-01']);
    writeEntry($this->dir, '2026-09-20-stub.php', ['name' => 'Stub', 'added' => '2026-09-21']);
    writeEntry($this->dir, '2026-03-01-archived.php', ['name' => 'Archived']);

    $main = pages($this)['index.html'];

    expect(texts($main, '#this-week article.lab h4'))->toBe(['Newer', 'Older', 'Before'])
        ->and(texts($main, '#this-week h3'))->toBe(['This week'])
        ->and(texts($main, '#this-week .bundle-note'))->toBe(['2 parcels have been added since Monday 12/10. The rest were added before.'])
        ->and(texts($main, '#manifest [data-view="labels"] .stub h4'))->toBe(['Stub'])
        ->and($main->querySelectorAll('#manifest .lab'))->toHaveCount(3)
        ->and($main->querySelectorAll('.lab-new'))->toHaveCount(1)
        ->and($main->querySelectorAll('.lab.newest'))->toHaveCount(1)
        ->and($main->querySelector('#manifest article')->classList->contains('newest'))->toBeTrue()
        ->and($main->querySelector('#manifest article')->querySelector('.lab-new'))->not->toBeNull();
});

it('marks the entry that shipped last as the newest, even when it is not the first label', function () {
    writeEntry($this->dir, '2026-09-01-added-last.php', ['name' => 'Added last', 'added' => '2026-10-15']);
    writeEntry($this->dir, '2026-10-10-shipped-last.php', ['name' => 'Shipped last', 'added' => '2026-10-14']);
    writeEntry($this->dir, '2026-08-01-third.php', ['name' => 'Third', 'added' => '2026-10-13']);

    $main = pages($this)['index.html'];

    expect(texts($main, '#this-week article.lab h4'))->toBe(['Added last', 'Shipped last', 'Third'])
        ->and(texts($main, '#this-week article.lab.newest h4'))->toBe(['Shipped last'])
        ->and($main->querySelectorAll('.lab-new'))->toHaveCount(1)
        ->and(texts($main, '#manifest [data-view="list"] .new-tag'))->toBe(['Newest'])
        ->and(texts($main, '#manifest [data-view="list"] tr:has(.new-tag) .name'))->toBe(['Shipped last Newest']);
});

it('marks the newest on its stub when the entry that shipped last is not on a full label', function () {
    writeEntry($this->dir, '2026-10-15-fresh.php', ['name' => 'Fresh', 'added' => '2026-10-15']);
    foreach (['a', 'b', 'c'] as $i => $name) {
        writeEntry($this->dir, '2026-09-0'.($i + 1)."-{$name}.php", ['name' => strtoupper($name), 'added' => '2026-10-16']);
    }

    $main = pages($this)['index.html'];

    expect(texts($main, '#this-week article.lab h4'))->not->toContain('Fresh')
        ->and($main->querySelectorAll('.lab.newest'))->toHaveCount(0)
        ->and($main->querySelectorAll('.lab-new'))->toHaveCount(0)
        ->and(texts($main, '.stub.newest h4'))->toBe(['Fresh'])
        ->and(texts($main, '.stub.newest .new-tag'))->toBe(['Newest'])
        ->and($main->querySelectorAll('.stub .new-tag'))->toHaveCount(1);
});

it('says how many full labels were added this week', function (array $added, string $title, string $note) {
    foreach ($added as $i => $day) {
        writeEntry($this->dir, '2026-09-0'.($i + 1)."-e{$i}.php", ['added' => $day]);
    }

    $main = pages($this)['index.html'];

    expect(texts($main, '#this-week h3'))->toBe([$title])
        ->and(texts($main, '#this-week .bundle-note'))->toBe([$note]);
})->with([
    'none' => [['2026-10-01', '2026-10-02', '2026-10-03'], 'Added last', 'Nothing has been added since Monday 12/10. These 3 were added last.'],
    'one' => [['2026-10-12', '2026-10-02', '2026-10-03'], 'This week', '1 parcel has been added since Monday 12/10. The rest were added before.'],
    'all three' => [['2026-10-12', '2026-10-13', '2026-10-16'], 'This week', 'All 3 have been added since Monday 12/10.'],
    'more' => [['2026-10-12', '2026-10-13', '2026-10-16', '2026-10-16'], 'This week', '4 parcels have been added since Monday 12/10. Here are the newest 3. The others are in the bundles below, by shipped date.'],
]);

it('bundles the other entries by the week or the month they shipped, with a count', function () {
    foreach (['2026-10-14' => 'a', '2026-10-13' => 'b', '2026-10-12' => 'c'] as $day => $slug) {
        writeEntry($this->dir, "{$day}-{$slug}.php", ['added' => '2026-10-16']);
    }
    writeEntry($this->dir, '2026-10-15-this-week.php', ['name' => 'This week', 'added' => '2026-10-15']);
    writeEntry($this->dir, '2026-10-06-week-41.php', ['name' => 'Week 41', 'added' => '2026-10-06']);
    writeEntry($this->dir, '2026-09-10-september.php', ['name' => 'September', 'added' => '2026-09-10']);
    writeEntry($this->dir, '2026-09-09-september-too.php', ['name' => 'September too', 'added' => '2026-09-10']);

    $labels = pages($this)['index.html']->querySelector('#manifest [data-view="labels"]');
    $bundles = iterator_to_array($labels->querySelectorAll('.bundle'));

    expect(array_map(fn ($b) => trim($b->querySelector('h3')->textContent), $bundles))
        ->toBe(['Week 42, 12/10 to 18/10', 'Week 41, 05/10 to 11/10', 'September 2026'])
        ->and(array_map(fn ($b) => trim($b->querySelector('[data-group-count]')->textContent), $bundles))
        ->toBe(['1 parcel', '1 parcel', '2 parcels'])
        ->and(array_map(fn ($b) => $b->getAttribute('id'), $bundles))->toBe(['week-2026-42', 'week-2026-41', 'month-2026-09'])
        ->and(array_map(fn ($b) => $b->hasAttribute('data-group'), $bundles))->toBe([true, true, true])
        ->and(array_map(fn ($b) => texts($labels->ownerDocument, '#'.$b->getAttribute('id').' .stub h4'), $bundles))
        ->toBe([['This week'], ['Week 41'], ['September', 'September too']])
        ->and($labels->querySelector('#this-week')->hasAttribute('data-group'))->toBeTrue()
        ->and(array_map(fn ($b) => $b->parentElement->classList->contains('weeks'), $bundles))->toBe([true, true, false])
        ->and($labels->querySelectorAll('.weeks'))->toHaveCount(1);
});

it('shows a stub with the route code, the name, the sentence, the makers and the date', function () {
    for ($day = 1; $day <= 3; $day++) {
        writeEntry($this->dir, "2026-10-0{$day}-new-{$day}.php", ['added' => '2026-10-16']);
    }
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'added' => '2026-09-02',
        'stack' => 'laravel',
        'makers' => [['name' => 'Nuno Maduro', 'github' => 'nunomaduro'], ['name' => 'Brent Roose']],
    ]);

    $stub = pages($this)['index.html']->querySelector('#manifest .stub');

    expect($stub->getAttribute('data-stack'))->toBe('laravel')
        ->and($stub->getAttribute('aria-labelledby'))->toBe($stub->querySelector('h4')->getAttribute('id'))
        ->and(trim($stub->querySelector('h4')->textContent))->toBe('Pest 5')
        ->and(trim($stub->querySelector('.route b')->textContent))->toBe('LRV')
        ->and($stub->querySelector('.route')->getAttribute('aria-label'))->toBe('Stack: Laravel')
        ->and(trim($stub->querySelector('.stub-sentence')->textContent))->toBe('A testing framework for PHP, focused on simplicity.')
        ->and(trim($stub->querySelector('.stub-from')->textContent))->toBe('Nuno Maduro, with Brent Roose')
        ->and($stub->querySelector('.stub-date time')->getAttribute('datetime'))->toBe('2026-09-01')
        ->and(trim($stub->querySelector('.stub-date time')->textContent))->toBe('01/09/2026')
        ->and($stub->querySelectorAll('.bars, .pm, .waves, .tid, .lab-new'))->toHaveCount(0);
});

it('links each stub to its entry page, or to its source while there are no entry pages', function () {
    for ($day = 1; $day <= 4; $day++) {
        writeEntry($this->dir, "2026-10-0{$day}-e-{$day}.php", ['added' => "2026-10-0{$day}"]);
    }

    $without = pages($this)['index.html']->querySelector('#manifest .stub .stub-more');
    $with = pages($this, entryPages: true)['index.html']->querySelector('#manifest .stub .stub-more');

    expect($with->getAttribute('href'))->toBe('/entries/e-1/')
        ->and(preg_replace('/\s+/', ' ', trim($with->textContent)))->toBe('Details about Pest 5')
        ->and($with->querySelector('.sr-only')?->textContent)->toBe(' about Pest 5')
        ->and($without->getAttribute('href'))->toBe('https://github.com/pestphp/pest')
        ->and(preg_replace('/\s+/', ' ', trim($without->textContent)))->toBe('Source of Pest 5');
});

it('groups the List view the same way as the labels', function () {
    writeEntry($this->dir, '2026-10-14-a.php', ['name' => 'A', 'added' => '2026-10-16']);
    writeEntry($this->dir, '2026-10-13-b.php', ['name' => 'B', 'added' => '2026-10-15']);
    writeEntry($this->dir, '2026-10-12-c.php', ['name' => 'C', 'added' => '2026-10-14']);
    writeEntry($this->dir, '2026-09-10-d.php', ['name' => 'D', 'added' => '2026-09-11']);

    $list = pages($this)['index.html']->querySelector('#manifest [data-view="list"]');
    $groups = iterator_to_array($list->querySelectorAll('tbody[data-group]'));

    expect(array_map(fn ($g) => trim($g->querySelector('.grp th')->firstChild->textContent), $groups))->toBe(['This week', 'September 2026'])
        ->and(array_map(fn ($g) => trim($g->querySelector('[data-group-count]')->textContent), $groups))->toBe(['3 parcels', '1 parcel'])
        ->and(array_map(fn ($g) => texts($list->ownerDocument, '#'.$g->getAttribute('id').' tr[data-stack] .no'), $groups))
        ->toBe([['004', '003', '002'], ['001']])
        ->and(texts($list->ownerDocument, '#manifest [data-view="list"] .new-tag'))->toBe(['Newest']);
});

it('groups the archive by month', function () {
    writeEntry($this->dir, '2026-03-20-a.php', ['name' => 'A']);
    writeEntry($this->dir, '2026-03-02-b.php', ['name' => 'B']);
    writeEntry($this->dir, '2026-01-02-c.php', ['name' => 'C']);

    $archive = pages($this)['archive/index.html'];

    expect(array_map(fn ($th) => trim($th->firstChild->textContent), iterator_to_array($archive->querySelectorAll('tbody[data-group] .grp th'))))
        ->toBe(['March 2026', 'January 2026'])
        ->and(texts($archive, 'tbody[data-group] [data-group-count]'))->toBe(['2 parcels', '1 parcel'])
        ->and(texts($archive, 'tbody .name'))->toBe(['A', 'B', 'C']);
});

it('links to the feeds page from the footer of every page', function (string $page) {
    $doc = pages($this)[$page];

    expect(hrefs($doc, 'footer .foot-pages a'))->toBe(['/privacy/', '/credits/', '/feeds/'])
        ->and(texts($doc, 'footer .foot-pages a'))->toBe(['Privacy', 'Credits', 'Feeds']);
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html', '404.html']);

it('lists the main feed and one feed per stack on the feeds page, most common stack first', function () {
    writeEntry($this->dir, '2026-09-01-a.php', ['stack' => 'symfony']);
    writeEntry($this->dir, '2026-09-02-b.php', ['stack' => 'laravel']);
    writeEntry($this->dir, '2026-09-03-c.php', ['stack' => 'laravel']);
    writeEntry($this->dir, '2026-01-03-old.php', ['stack' => 'wordpress']);

    $doc = pages($this)['feeds/index.html'];

    expect($doc->querySelector('title')->textContent)->toBe('phplives.dev: feeds')
        ->and(texts($doc, 'h1'))->toBe(['Feeds'])
        ->and(texts($doc, '.feeds .fd-name'))->toBe(['Every entry', 'LRV Laravel', 'SYM Symfony', 'WRD WordPress'])
        ->and(hrefs($doc, '.feeds .fd-name a'))->toBe([
            'https://phplives.dev/feed.xml',
            'https://phplives.dev/feed/laravel.xml',
            'https://phplives.dev/feed/symfony.xml',
            'https://phplives.dev/feed/wordpress.xml',
        ])
        ->and(array_map(fn ($b) => $b->getAttribute('data-copy'), iterator_to_array($doc->querySelectorAll('.feeds button[data-copy]'))))->toBe([
            'https://phplives.dev/feed.xml',
            'https://phplives.dev/feed/laravel.xml',
            'https://phplives.dev/feed/symfony.xml',
            'https://phplives.dev/feed/wordpress.xml',
        ]);
});

it('lists one feed per topic on the feeds page, after the stacks, most common topic first', function () {
    writeEntry($this->dir, '2026-09-01-a.php', ['stack' => 'laravel', 'topics' => ['testing']]);
    writeEntry($this->dir, '2026-09-02-b.php', ['stack' => 'laravel', 'topics' => ['testing', 'ai']]);
    writeEntry($this->dir, '2026-09-03-c.php', ['stack' => 'laravel', 'topics' => ['ai', 'testing']]);
    writeEntry($this->dir, '2026-09-04-d.php', ['stack' => 'laravel', 'topics' => ['security']]);

    $doc = pages($this)['feeds/index.html'];

    expect(texts($doc, '.feeds .fd-name'))->toBe(['Every entry', 'LRV Laravel', 'Testing', 'AI'])
        ->and(hrefs($doc, '.feeds .fd-name a'))->toBe([
            'https://phplives.dev/feed.xml',
            'https://phplives.dev/feed/laravel.xml',
            'https://phplives.dev/feed/topic/testing.xml',
            'https://phplives.dev/feed/topic/ai.xml',
        ])
        ->and(texts($doc, '.feeds .fd-use')[2])->toBe('Only the entries with the topic Testing.')
        ->and(texts($doc, '.feeds h3'))->toBe(['By stack', 'By topic'])
        ->and(texts($doc, '.feeds .sheet-head .mono'))->toBe(['4 feeds']);
});

it('gives the feed links room for the longest topic feed', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    expect($css)->toContain('.fd-list li{display:grid;grid-template-columns:minmax(0,13rem) minmax(0,1fr) minmax(0,27rem);');
});

it('says how to use a feed on the feeds page', function () {
    $doc = pages($this)['feeds/index.html'];

    expect(texts($doc, '.head p'))->toBe(['Follow new entries in your feed reader. Copy a link below and paste it into your reader.'])
        ->and(texts($doc, '.feeds .fd-use')[0])->toBe('The 50 newest entries, in the order they were added to the site.')
        ->and(texts($doc, '.feeds .sheet-foot'))->toBe(['A new entry shows in the feeds on the day it is added. The feeds use the Atom format. Every feed reader can read it.']);
});

it('runs the copy script on the feeds page', function () {
    $scripts = array_map(fn ($s) => $s->textContent, iterator_to_array(pages($this)['feeds/index.html']->body->querySelectorAll('script:not([src])')));

    expect($scripts)->toHaveCount(1)
        ->and($scripts[0])->toContain('data-copy');
});

it('points to the feeds from the head of the main list', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $doc = pages($this)['index.html'];

    expect(hrefs($doc, '#manifest .sec-head a'))->toBe(['/feeds/'])
        ->and(texts($doc, '#manifest .sec-head a'))->toBe(['Follow new entries by RSS']);
});

it('shows every part of an entry', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', [
        'makers' => [
            ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
            ['name' => 'Brent Roose', 'github' => 'brendt'],
        ],
        'stack' => 'laravel',
    ]);

    $entry = pages($this)['index.html']->querySelector('#manifest article');

    expect($entry->querySelector('h4')->textContent)->toBe('Pest 5')
        ->and($entry->querySelector('.contents p')->textContent)->toBe('A testing framework for PHP, focused on simplicity.')
        ->and(trim($entry->querySelector('.date')->textContent))->toBe('01/09/2026')
        ->and($entry->querySelector('.route b')->textContent)->toBe('LRV')
        ->and($entry->querySelector('.route span')->textContent)->toBe('Laravel')
        ->and($entry->querySelector('.proof a')->getAttribute('href'))->toBe('https://github.com/pestphp/pest/releases/tag/v5.0.0')
        ->and(trim($entry->querySelector('.proof')->textContent))->toBe('Date from: release')
        ->and($entry->querySelector('.track a')->getAttribute('href'))->toBe('https://github.com/pestphp/pest')
        ->and(trim($entry->querySelector('.track a')->textContent))->toBe('github.com/pestphp/pest');

    expect(texts($entry->ownerDocument, '#manifest .makers li'))->toBe(['Nuno Maduro', 'Brent Roose'])
        ->and(hrefs($entry->ownerDocument, '#manifest .makers .who'))
        ->toBe(['https://github.com/nunomaduro', 'https://github.com/brendt']);
});

it('links a maker to /dev/null only when they have a profile there', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['makers' => [
        ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
        ['name' => 'Brent Roose', 'github' => 'brendt'],
    ]]);

    $entry = pages($this, ['nunomaduro'])['index.html']->querySelector('#manifest article');

    $doc = $entry->ownerDocument;

    expect(hrefs($doc, '#manifest .makers .devnull'))->toBe(['https://dev-null.sh/@nunomaduro'])
        ->and($doc->querySelector('#manifest .makers .devnull')->getAttribute('aria-label'))->toBe('Nuno Maduro on /dev/null')
        ->and(texts($doc, '#manifest .makers li'))->toBe(['Nuno Maduro /dev/null', 'Brent Roose']);
});

it('matches /dev/null usernames without caring about case', function () {
    writeEntry($this->dir, '2026-09-01-superstack.php', ['makers' => [['name' => 'NativePHP', 'github' => 'NativePHP']]]);

    expect(hrefs(pages($this, ['nativephp'])['index.html'], '#manifest .devnull'))
        ->toBe(['https://dev-null.sh/@NativePHP']);
});

it('shows a maker without a GitHub username', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['makers' => [['name' => 'coolLabs']]]);

    $entry = pages($this)['index.html']->querySelector('#manifest article');

    expect(texts($entry->ownerDocument, '#manifest .makers li'))->toBe(['coolLabs'])
        ->and($entry->querySelectorAll('.makers a'))->toHaveCount(0);
});

it('escapes entry text', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['name' => '<script>alert(1)</script>']);

    $main = pages($this)['index.html'];

    expect(texts($main, '#manifest h4'))->toBe(['<script>alert(1)</script>'])
        ->and($main->querySelectorAll('#manifest script'))->toHaveCount(0);
});

it('sums up the manifest', function () {
    writeEntry($this->dir, '2026-10-01-a.php', ['stack' => 'laravel']);
    writeEntry($this->dir, '2026-09-01-b.php', ['stack' => 'laravel']);
    writeEntry($this->dir, '2026-08-01-c.php', ['stack' => 'php']);

    $slip = pages($this)['index.html'];

    expect(array_map(fn ($dt, $dd) => "{$dt} {$dd}", texts($slip, '.slip dt'), texts($slip, '.slip dd')))->toBe([
        'Parcels 3',
        'Shipped since 16/04/2026',
        'Last shipped 01/10/2026',
        'Stacks 2 Laravel, 1 plain PHP',
    ])->and(texts($slip, '#manifest-title'))->toBe(['Shipped since 16/04/2026']);
});

it('points the manifest to the archive when it has parcels, with the parcels still counted apart', function () {
    writeEntry($this->dir, '2026-10-01-a.php');
    writeEntry($this->dir, '2026-03-01-old.php');
    writeEntry($this->dir, '2026-02-01-older.php');

    $slip = pages($this)['index.html'];

    expect(array_map(fn ($dt, $dd) => "{$dt} {$dd}", texts($slip, '.slip dt'), texts($slip, '.slip dd')))->toBe([
        'Parcels 1',
        'Shipped since 16/04/2026',
        'Last shipped 01/10/2026',
        'Stacks 1 plain PHP',
        'Archive 2 older parcels',
    ])->and(hrefs($slip, '.slip-archive a'))->toBe(['/archive/']);
});

it('says one older parcel in the singular', function () {
    writeEntry($this->dir, '2026-10-01-a.php');
    writeEntry($this->dir, '2026-03-01-old.php');

    expect(texts(pages($this)['index.html'], '.slip-archive dd'))->toBe(['1 older parcel']);
});

it('leaves the archive out of the manifest while it is empty', function () {
    writeEntry($this->dir, '2026-10-01-a.php');

    expect(pages($this)['index.html']->querySelector('.slip-archive'))->toBeNull();
});

it('says so when there are no current entries', function () {
    $main = pages($this)['index.html'];

    expect($main->querySelectorAll('#manifest article'))->toHaveCount(0)
        ->and(texts($main, '#manifest .empty'))->toBe(['Nothing has shipped in the last 6 months yet.']);
});

it('lists archived entries in the archive, newest first', function () {
    writeEntry($this->dir, '2026-10-01-current.php', ['name' => 'Current']);
    writeEntry($this->dir, '2026-02-01-old.php', ['name' => 'Old']);
    writeEntry($this->dir, '2026-03-01-less-old.php', ['name' => 'Less old', 'checked' => '2026-10-16']);

    $archive = pages($this)['archive/index.html'];

    expect(texts($archive, 'tbody .name'))->toBe(['Less old', 'Old'])
        ->and(texts($archive, 'tbody .date'))->toBe(['01/03/2026', '01/02/2026'])
        ->and(texts($archive, '#sheet-title'))->toBe(['Shipped before 16/04/2026']);
});

it('says so when the archive is empty', function () {
    writeEntry($this->dir, '2026-10-01-current.php');

    $archive = pages($this)['archive/index.html'];

    expect($archive->querySelectorAll('table'))->toHaveCount(0)
        ->and(texts($archive, '.sheet .empty'))->toBe(['No entry is older than 6 months yet.']);
});

it('numbers entries in the order they shipped', function () {
    writeEntry($this->dir, '2026-08-01-first.php');
    writeEntry($this->dir, '2026-09-01-second.php');

    expect(texts(pages($this)['index.html'], '#manifest .tid'))->toBe(['2026090102', '2026080101']);
});

it('has the same frame on every page', function (string $page) {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $doc = pages($this)[$page];

    expect($doc->documentElement->getAttribute('lang'))->toBe('en')
        ->and($doc->querySelector('meta[name="viewport"]'))->not->toBeNull()
        ->and($doc->querySelector('title')->textContent)->toStartWith('phplives.dev')
        ->and(hrefs($doc, 'footer a'))->toContain(
            'https://whyphp.dev',
            'https://albertoarena.it/?utm_source=phplives.dev&utm_medium=footer',
            'https://cogway.dev/?utm_source=phplives.dev&utm_medium=footer',
        )
        ->and(texts($doc, '.theme button'))->toBe(['Light', 'Dark', 'System'])
        ->and(hrefs($doc, '.nav a'))->toContain('/', '/archive/');
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('loads Cloudflare Web Analytics, and nothing else that tracks', function (string $page) {
    $doc = pages($this)[$page];
    $beacons = $doc->querySelectorAll('script[src="https://static.cloudflareinsights.com/beacon.min.js"]');

    expect($beacons)->toHaveCount(1)
        ->and($beacons[0]->getAttribute('type'))->toBe('module')
        ->and(json_decode($beacons[0]->getAttribute('data-cf-beacon'), true))->toBe(['token' => Site::ANALYTICS_TOKEN])
        ->and(Site::ANALYTICS_TOKEN)->toMatch('/^[0-9a-f]{32}$/')
        ->and($doc->querySelectorAll('script[src]'))->toHaveCount(1);
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('sets the theme before the page paints', function (string $page) {
    $head = pages($this)[$page]->head;
    $script = $head->querySelector('script');

    expect($script)->not->toBeNull()
        ->and($script->hasAttribute('src'))->toBeFalse()
        ->and($script->textContent)->toContain('localStorage')
        ->and($head->querySelector('script ~ link[rel="stylesheet"][href="/site.css"]'))->not->toBeNull();
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('marks the current page in the nav', function () {
    $pages = pages($this);

    expect(hrefs($pages['index.html'], '.nav a[aria-current="page"]'))->toBe(['/'])
        ->and(hrefs($pages['archive/index.html'], '.nav a[aria-current="page"]'))->toBe(['/archive/']);
});

it('only links out over HTTPS', function (string $page) {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    $external = array_filter(
        hrefs(pages($this)[$page], 'a[href]'),
        fn ($h) => ! str_starts_with($h, '/') && ! str_starts_with($h, '#') && ! str_starts_with($h, 'mailto:'),
    );

    expect($external)->not->toBeEmpty()->each->toStartWith('https://');
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('has no em dashes on any page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    writeEntry($this->dir, '2026-02-01-old.php');

    foreach ((new Site(Entries::load($this->dir, $this->today), $this->today))->pages() as $html) {
        expect($html)->not->toContain("\u{2014}");
    }
});

it('invites makers to commit on /dev/null', function () {
    expect(hrefs(pages($this)['index.html'], '#devnull a'))->toContain('https://dev-null.sh');
});

it('shows the posts that link to phplives.dev in a dispatch log', function () {
    $posts = [
        new Post('https://dev-null.sh/@albertoarena/commits/c2', "Shipped with PHP: Pest 5.\n\nSee more at https://phplives.dev", new DateTimeImmutable('2026-10-16T09:00:00Z')),
        new Post('https://dev-null.sh/@albertoarena/commits/c1', 'Shipped with PHP: <Symfony 8.1>', new DateTimeImmutable('2026-10-15T09:00:00Z')),
    ];

    $strip = pages($this, posts: $posts)['index.html']->querySelector('#devnull .strip');

    expect($strip->ownerDocument->querySelectorAll('#devnull .dn-solo'))->toHaveCount(0);

    expect(texts($strip->ownerDocument, '#devnull .strip h3'))->toBe(['Dispatch log'])
        ->and(texts($strip->ownerDocument, '#devnull .post .meta'))->toBe(['16/10/2026', '15/10/2026'])
        ->and(hrefs($strip->ownerDocument, '#devnull .post .meta a'))->toBe([
            'https://dev-null.sh/@albertoarena/commits/c2',
            'https://dev-null.sh/@albertoarena/commits/c1',
        ])
        ->and(array_map(fn ($t) => $t->getAttribute('datetime'), iterator_to_array($strip->querySelectorAll('.post .meta a time'))))->toBe(['2026-10-16', '2026-10-15'])
        ->and(array_map(fn ($p) => $p->textContent, iterator_to_array($strip->querySelectorAll('.post .body'))))->toBe([
            "Shipped with PHP: Pest 5.\n\nSee more at https://phplives.dev",
            'Shipped with PHP: <Symfony 8.1>',
        ])
        ->and(hrefs($strip->ownerDocument, '#devnull .strip-foot a'))->toBe(['https://dev-null.sh/@albertoarena']);
});

it('hides the dispatch log when there are no posts', function () {
    $main = pages($this)['index.html'];

    expect($main->querySelectorAll('#devnull .strip'))->toHaveCount(0)
        ->and($main->querySelectorAll('#devnull .dn-solo'))->toHaveCount(1)
        ->and(hrefs($main, '#devnull a'))->toContain('https://dev-null.sh');
});

it('ends the /dev/null band with the wallpaper, linked to the profile', function (array $posts) {
    $main = pages($this, posts: $posts)['index.html'];
    $link = $main->querySelector('#devnull .dn-wall a');
    $img = $link?->querySelector('img');

    expect($link?->getAttribute('href'))->toBe('https://dev-null.sh/@albertoarena')
        ->and($link->getAttribute('aria-label'))->toBe('Follow @albertoarena on /dev/null')
        ->and($img->getAttribute('src'))->toBe('/img/devnull-wallpaper-log-1920.webp')
        ->and($img->getAttribute('srcset'))->toBe('/img/devnull-wallpaper-log-1280.webp 1280w, /img/devnull-wallpaper-log-1920.webp 1920w')
        ->and($img->getAttribute('alt'))->toBe('')
        ->and($img->getAttribute('loading'))->toBe('lazy')
        ->and([$img->getAttribute('width'), $img->getAttribute('height')])->toBe(['1920', '1080'])
        ->and(texts($main, '#devnull .dn-wall-credit'))->toBe(['Wallpaper from the /dev/null brand kit.'])
        ->and(hrefs($main, '#devnull .dn-wall-credit a'))->toBe(['https://dev-null.sh/brand#wallpapers']);
})->with([
    'with posts' => [[new Post('https://dev-null.sh/@albertoarena/commits/c1', 'See https://phplives.dev', new DateTimeImmutable('2026-10-16'))]],
    'without posts' => [[]],
]);

it('says in every footer that /dev/null does not sponsor the site', function (string $page) {
    expect(texts(pages($this)[$page], 'footer .indie'))
        ->toBe(['phplives.dev is an independent site. /dev/null does not sponsor or endorse it.']);
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html', '404.html']);

it('describes itself for link previews', function (string $page, string $url) {
    $doc = pages($this)[$page];
    $meta = fn (string $selector) => $doc->querySelector($selector)?->getAttribute('content');

    expect($meta('meta[property="og:title"]'))->toBe($doc->querySelector('title')->textContent)
        ->and($meta('meta[property="og:description"]'))->toBe($meta('meta[name="description"]'))
        ->and(mb_strlen($meta('meta[name="description"]')))->toBeGreaterThanOrEqual(100)
        ->and($meta('meta[name="author"]'))->toBe('Alberto Arena')
        ->and($meta('meta[property="og:url"]'))->toBe($url)
        ->and($meta('meta[property="og:type"]'))->toBe('website')
        ->and($meta('meta[property="og:site_name"]'))->toBe('phplives.dev')
        ->and($meta('meta[property="og:image"]'))->toBe('https://phplives.dev/share.png?v=3')
        ->and($meta('meta[property="og:image:width"]'))->toBe('1200')
        ->and($meta('meta[property="og:image:height"]'))->toBe('630')
        ->and($meta('meta[property="og:image:alt"]'))->toBe('PHP delivers. PHP lives! Real things people shipped with PHP in the last 6 months. phplives.dev')
        ->and($meta('meta[name="twitter:card"]'))->toBe('summary_large_image');
})->with([
    ['index.html', 'https://phplives.dev/'],
    ['archive/index.html', 'https://phplives.dev/archive/'],
    ['privacy/index.html', 'https://phplives.dev/privacy/'],
    ['credits/index.html', 'https://phplives.dev/credits/'],
    ['feeds/index.html', 'https://phplives.dev/feeds/'],
]);

it('has a favicon', function (string $page) {
    expect(hrefs(pages($this)[$page], 'link[rel="icon"]'))->toBe(['/favicon.svg']);
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('links to the author on Mastodon with rel me', function (string $page) {
    expect(hrefs(pages($this)[$page], 'link[rel="me"]'))->toBe(['https://phpc.social/@albertoarena']);
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('shows how to commit on /dev/null from a terminal', function (array $posts) {
    $doc = pages($this, posts: $posts)['index.html'];
    $command = 'curl -fsSL https://dev-null.sh/commit | bash';
    $code = $doc->querySelector('#devnull .cli-line code');
    $button = $doc->querySelector('#devnull button[data-copy]');

    expect(trim(str_replace($code->querySelector('[aria-hidden="true"]')->textContent, '', $code->textContent)))->toBe($command)
        ->and($button->getAttribute('data-copy'))->toBe($command)
        ->and($button->hasAttribute('hidden'))->toBeTrue()
        ->and($doc->getElementById($button->getAttribute('data-copy-status'))?->getAttribute('role'))->toBe('status')
        ->and(texts($doc, '#devnull .cli-note'))->toBe(['It asks for your message. You need a /dev/null account and an SSH key on your GitHub account.']);
})->with([
    'without posts' => [[]],
    'with posts' => [[new Post('https://dev-null.sh/@albertoarena/commits/c1', 'See https://phplives.dev', new DateTimeImmutable('2026-10-16'))]],
]);

it('runs the copy and the manifest scripts inline, at the end of the main page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');
    $scripts = array_map(fn ($s) => $s->textContent, iterator_to_array(pages($this)['index.html']->body->querySelectorAll('script:not([src])')));

    expect($scripts)->toHaveCount(2)
        ->and($scripts[0])->toContain('data-copy')
        ->and($scripts[1])->toContain('data-stack');
});

it('links to the privacy page from the footer of every page', function (string $page) {
    expect(hrefs(pages($this)[$page], 'footer a'))->toContain('/privacy/');
})->with(['index.html', 'archive/index.html', 'privacy/index.html']);

it('says what the site collects on the privacy page', function () {
    $doc = pages($this)['privacy/index.html'];

    expect($doc->querySelector('title')->textContent)->toBe('phplives.dev: privacy')
        ->and(texts($doc, 'h1'))->toBe(['Privacy'])
        ->and(texts($doc, '.policy h2'))->toBe([
            'In short',
            'Data controller',
            'Analytics',
            'Hosting',
            'Fonts',
            'Legal basis',
            'What stays in your browser',
            'What the site does not do',
            'Your rights',
            'Links to other sites',
            'Changes to this page',
        ])
        ->and(hrefs($doc, '.policy a'))->toContain(
            'https://developers.cloudflare.com/web-analytics/about/',
            'https://developers.cloudflare.com/speed/observatory/rum-beacon/',
            'https://developers.cloudflare.com/web-analytics/data-metrics/dimensions/',
            'https://www.cloudflare.com/privacypolicy/',
            'https://albertoarena.it/?utm_source=phplives.dev&utm_medium=footer',
        )
        ->and(texts($doc, '.policy .updated'))->toBe(['Last updated: 17/10/2026']);
});

it('has no copy script on the privacy page', function () {
    expect(pages($this)['privacy/index.html']->body->querySelectorAll('script:not([src])'))->toHaveCount(0);
});

it('covers the GDPR on the privacy page', function () {
    $privacy = pages($this)['privacy/index.html'];

    expect(texts($privacy, '.policy h2'))->toContain('Data controller', 'Legal basis', 'Your rights')
        ->and(hrefs($privacy, '.policy a[href^="mailto:"]'))->toContain('mailto:hello@albertoarena.it')
        ->and($privacy->querySelector('.policy')->textContent)->toContain('Art. 6(1)(f)');
});

it('links to the credits from the footer of every page', function (string $page) {
    expect(hrefs(pages($this)[$page], 'footer a[href="/credits/"]'))->toHaveCount(1)
        ->and(texts(pages($this)[$page], 'footer a[href="/credits/"]'))->toBe(['Credits']);
})->with(['index.html', 'archive/index.html', 'credits/index.html', 'feeds/index.html']);

it('credits the software, services and data the site uses', function () {
    $doc = pages($this)['credits/index.html'];

    expect($doc->querySelector('title')->textContent)->toBe('phplives.dev: credits')
        ->and(texts($doc, 'h1'))->toBe(['Credits'])
        ->and(texts($doc, '.credits .item'))->toBe([
            'Archivo', 'JetBrains Mono',
            'Octicons',
            'PHP', 'fontTools', 'Cloudflare Workers static assets', 'Wrangler', 'GitHub Actions', 'Cloudflare Web Analytics',
            'Pest', 'Vitest',
            '/dev/null',
            '/dev/null wallpaper',
        ])
        ->and(hrefs($doc, '.credits .item a'))->toBe([
            'https://fonts.google.com/specimen/Archivo',
            'https://fonts.google.com/specimen/JetBrains+Mono',
            'https://primer.style/octicons/',
            'https://www.php.net',
            'https://github.com/fonttools/fonttools',
            'https://developers.cloudflare.com/workers/static-assets/',
            'https://developers.cloudflare.com/workers/wrangler/',
            'https://github.com/features/actions',
            'https://www.cloudflare.com/web-analytics/',
            'https://pestphp.com',
            'https://vitest.dev',
            'https://dev-null.sh',
            'https://dev-null.sh/brand#wallpapers',
        ]);
});

it('names the makers and licences of the fonts', function () {
    $doc = pages($this)['credits/index.html'];

    expect(texts($doc, '#credits-fonts .cr-from'))->toBe([
        'Omnibus-Type',
        'JetBrains, Philipp Nurullin and Konstantin Bulenkov',
    ])->and(texts($doc, '#credits-fonts .cr-lic'))->toBe(['SIL Open Font License 1.1', 'SIL Open Font License 1.1']);
});

it('says the fonts also draw the share images', function () {
    $doc = pages($this)['credits/index.html'];

    expect(texts($doc, '#credits-fonts .use'))->each->toContain('share image')
        ->and(texts($doc, '#credits-fonts .cr-note')[0])->toContain('assets/fonts/card/')
        ->and(texts($doc, '#credits-build .use')[0])->toContain('GD');
});

it('says when the credits were last updated', function () {
    expect(texts(pages($this)['credits/index.html'], '.credits .updated'))->toBe(['Last updated 20/10/2026']);
});

function stackEntries(object $test): void
{
    writeEntry($test->dir, '2026-10-01-a.php', ['name' => 'A', 'stack' => 'php']);
    writeEntry($test->dir, '2026-09-01-b.php', ['name' => 'B', 'stack' => 'laravel']);
    writeEntry($test->dir, '2026-08-01-c.php', ['name' => 'C', 'stack' => 'laravel']);
    writeEntry($test->dir, '2026-02-01-old.php', ['name' => 'Old', 'stack' => 'symfony']);
}

it('shows the main list as labels and as a list, each entry tagged with its stack', function () {
    stackEntries($this);
    $main = pages($this)['index.html'];
    $labels = $main->querySelector('#manifest [data-view="labels"]');
    $list = $main->querySelector('#manifest [data-view="list"]');

    expect(array_map(fn ($a) => $a->getAttribute('data-stack'), iterator_to_array($labels->querySelectorAll('article'))))->toBe(['php', 'laravel', 'laravel'])
        ->and(array_map(fn ($r) => $r->getAttribute('data-stack'), iterator_to_array($list->querySelectorAll('tbody tr[data-stack]'))))->toBe(['php', 'laravel', 'laravel'])
        ->and(texts($main, '#manifest [data-view="list"] tbody .name'))->toBe(['A Newest', 'B', 'C'])
        ->and($labels->hasAttribute('hidden'))->toBeFalse()
        ->and($list->hasAttribute('hidden'))->toBeTrue();
});

it('has a Labels / List toggle on the main page only', function () {
    stackEntries($this);
    $pages = pages($this);

    expect(texts($pages['index.html'], '[data-view-choice]'))->toBe(['Labels', 'List'])
        ->and(array_map(fn ($b) => $b->getAttribute('aria-pressed'), iterator_to_array($pages['index.html']->querySelectorAll('[data-view-choice]'))))->toBe(['true', 'false'])
        ->and($pages['archive/index.html']->querySelectorAll('[data-view-choice]'))->toHaveCount(0);
});

it('filters by stack, most common first, with counts', function () {
    stackEntries($this);
    $main = pages($this)['index.html'];
    $chips = iterator_to_array($main->querySelectorAll('#manifest [data-stack-choice]'));

    expect(texts($main, '#manifest [data-stack-choice]'))->toBe(['All 3', 'LRV 2', 'PHP 1'])
        ->and(array_map(fn ($b) => $b->getAttribute('data-stack-choice'), $chips))->toBe(['', 'laravel', 'php'])
        ->and(array_map(fn ($b) => $b->getAttribute('data-stack-label'), $chips))->toBe(['', 'Laravel', 'plain PHP'])
        ->and(array_map(fn ($b) => $b->getAttribute('aria-pressed'), $chips))->toBe(['true', 'false', 'false'])
        ->and($main->querySelector('#manifest [data-tools]')->hasAttribute('hidden'))->toBeTrue()
        ->and($main->querySelector('#manifest [data-filter-status]')->getAttribute('role'))->toBe('status');
});

it('filters the archive too', function () {
    stackEntries($this);
    $archive = pages($this)['archive/index.html'];

    expect(texts($archive, '[data-stack-choice]'))->toBe(['All 1', 'SYM 1'])
        ->and(array_map(fn ($r) => $r->getAttribute('data-stack'), iterator_to_array($archive->querySelectorAll('tbody tr[data-stack]'))))->toBe(['symfony'])
        ->and($archive->querySelector('[data-filter-status]')->getAttribute('role'))->toBe('status')
        ->and($archive->body->querySelectorAll('script:not([src])'))->toHaveCount(1);
});

it('turns the filter into a dropdown when there are too many stacks', function () {
    $site = new Site(Entries::load($this->dir, $this->today), $this->today);
    $html = $site->filterControl(['laravel' => 2, 'symfony' => 1, 'php' => 1], maxChips: 2);
    $doc = HTMLDocument::createFromString('<!doctype html><body>'.$html.'</body>', LIBXML_NOERROR);
    $select = $doc->querySelector('select[data-stack-select]');

    expect($doc->querySelectorAll('[data-stack-choice]'))->toHaveCount(0)
        ->and(texts($doc, 'select option'))->toBe(['All (4)', 'Laravel (2)', 'Symfony (1)', 'plain PHP (1)'])
        ->and(array_map(fn ($o) => $o->getAttribute('value'), iterator_to_array($doc->querySelectorAll('select option'))))->toBe(['', 'laravel', 'symfony', 'php'])
        ->and($doc->querySelector('label[for="'.$select->getAttribute('id').'"]')?->textContent)->toBe('Stack');
});

it('shows chips up to six stacks', function () {
    expect(Site::MAX_CHIPS)->toBe(6);
});

function topicEntries(object $test): void
{
    writeEntry($test->dir, '2026-10-01-a.php', ['name' => 'A', 'stack' => 'php', 'topics' => ['testing', 'devtools']]);
    writeEntry($test->dir, '2026-09-01-b.php', ['name' => 'B', 'stack' => 'laravel', 'topics' => ['mcp', 'testing']]);
    writeEntry($test->dir, '2026-08-01-c.php', ['name' => 'C', 'stack' => 'laravel', 'topics' => ['mcp', 'ai']]);
    writeEntry($test->dir, '2026-07-01-d.php', ['name' => 'D', 'stack' => 'laravel', 'topics' => ['mcp']]);
    writeEntry($test->dir, '2026-06-01-e.php', ['name' => 'E', 'stack' => 'php']);
    writeEntry($test->dir, '2026-02-01-old.php', ['name' => 'Old', 'stack' => 'symfony', 'topics' => ['ai']]);
    writeEntry($test->dir, '2026-01-01-older.php', ['name' => 'Older', 'stack' => 'symfony', 'topics' => ['ai', 'testing']]);
}

it('tags each entry with its topics, in both views', function () {
    topicEntries($this);
    $main = pages($this)['index.html'];
    $topics = fn (string $selector) => array_map(fn ($n) => $n->getAttribute('data-topics'), iterator_to_array($main->querySelectorAll($selector)));

    expect($topics('#manifest [data-view="labels"] [data-stack]'))->toBe(['testing devtools', 'mcp testing', 'mcp ai', 'mcp', ''])
        ->and($topics('#manifest [data-view="list"] tbody tr[data-stack]'))->toBe(['testing devtools', 'mcp testing', 'mcp ai', 'mcp', '']);
});

it('filters by topic, most common first, with counts, and only topics with 2 entries or more', function () {
    topicEntries($this);
    $main = pages($this)['index.html'];
    $chips = iterator_to_array($main->querySelectorAll('#manifest [data-topic-choice]'));

    expect(texts($main, '#manifest [data-topic-choice]'))->toBe(['All 5', 'MCP 3', 'Testing 2'])
        ->and(array_map(fn ($b) => $b->getAttribute('data-topic-choice'), $chips))->toBe(['', 'mcp', 'testing'])
        ->and(array_map(fn ($b) => $b->getAttribute('data-topic-label'), $chips))->toBe(['', 'MCP', 'Testing'])
        ->and(array_map(fn ($b) => $b->getAttribute('aria-pressed'), $chips))->toBe(['true', 'false', 'false'])
        ->and($main->querySelector('#manifest [aria-label="Filter by topic"] .filter-cap')->textContent)->toBe('Topic')
        ->and($main->querySelectorAll('#manifest [data-tools] [data-stack-choice]'))->not->toHaveCount(0);
});

it('counts the topics of the archive on their own', function () {
    topicEntries($this);
    $archive = pages($this)['archive/index.html'];

    expect(texts($archive, '[data-topic-choice]'))->toBe(['All 2', 'AI 2']);
});

it('has no topic filter when no topic has 2 entries', function () {
    stackEntries($this);
    $pages = pages($this);

    expect($pages['index.html']->querySelectorAll('[data-topic-choice], [data-topic-select]'))->toHaveCount(0)
        ->and($pages['archive/index.html']->querySelectorAll('[data-topic-choice], [data-topic-select]'))->toHaveCount(0);
});

it('turns the topic filter into a dropdown when there are too many topics', function () {
    $site = new Site(Entries::load($this->dir, $this->today), $this->today);
    $html = $site->filterControl(['mcp' => 3, 'testing' => 2, 'ai' => 2], maxChips: 2, kind: 'topic');
    $doc = HTMLDocument::createFromString('<!doctype html><body>'.$html.'</body>', LIBXML_NOERROR);
    $select = $doc->querySelector('select[data-topic-select]');

    expect($doc->querySelectorAll('[data-topic-choice]'))->toHaveCount(0)
        ->and(texts($doc, 'select option'))->toBe(['All (7)', 'MCP (3)', 'Testing (2)', 'AI (2)'])
        ->and(array_map(fn ($o) => $o->getAttribute('value'), iterator_to_array($doc->querySelectorAll('select option'))))->toBe(['', 'mcp', 'testing', 'ai'])
        ->and(array_map(fn ($o) => $o->getAttribute('data-topic-label'), iterator_to_array($doc->querySelectorAll('select option'))))->toBe(['', 'MCP', 'Testing', 'AI'])
        ->and($doc->querySelector('label[for="'.$select->getAttribute('id').'"]')?->textContent)->toBe('Topic')
        ->and($select->getAttribute('id'))->not->toBe('stack-filter');
});

it('invites everyone to contribute in every footer', function (string $page) {
    $doc = pages($this)[$page];
    $open = $doc->querySelector('footer .foot-open');

    expect(trim($open->querySelector('h2')->textContent))->toBe('Open source. Anyone can help.')
        ->and(trim(preg_replace('/\s+/', ' ', $open->querySelector('p')->textContent)))
        ->toBe('Add a project, fix a bug or improve the code. Every change is a pull request on GitHub.')
        ->and(hrefs($doc, 'footer .foot-open a'))->toBe([
            'https://github.com/albertoarena/phplives.dev',
            'https://github.com/albertoarena/phplives.dev/blob/main/CONTRIBUTING.md',
        ])
        ->and(texts($doc, 'footer .foot-open a'))->toBe(['See the code on GitHub', 'Read how to contribute'])
        ->and($open->querySelector('a.tag svg.gh')?->getAttribute('aria-hidden'))->toBe('true')
        ->and(hrefs($doc, 'footer .foot-pages a'))->toBe(['/privacy/', '/credits/', '/feeds/']);
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('asks visitors on the main page to add their own project', function () {
    $doc = pages($this)['index.html'];
    $send = $doc->querySelector('.hero .send');

    expect(hrefs($doc, '.hero .send a'))->toBe([
        'https://github.com/albertoarena/phplives.dev/blob/main/CONTRIBUTING.md',
        'https://github.com/albertoarena/phplives.dev/issues/new?template=suggest-a-project.yml',
    ])
        ->and(texts($doc, '.hero .send a'))->toBe(['Add your project', 'or suggest one in an issue'])
        ->and($send->querySelector('a.tag svg.gh')?->getAttribute('aria-hidden'))->toBe('true')
        ->and(trim(preg_replace('/\s+/', ' ', $doc->querySelector('.hero .send-note')->textContent)))
        ->toBe('Open source on GitHub. Anyone can send a project, and the editor checks each one.')
        ->and($doc->querySelectorAll('.hero .open, .soon'))->toHaveCount(0);
});

it('serves its own fonts, so no page loads anything from Google', function (string $page) {
    $doc = pages($this)[$page];
    $links = array_map(fn ($l) => $l->getAttribute('href'), iterator_to_array($doc->querySelectorAll('link')));

    expect(array_filter($links, fn ($h) => str_contains($h, 'google')))->toBe([])
        ->and($doc->saveHtml())->not->toContain('fonts.gstatic.com')->not->toContain('fonts.googleapis.com');
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('declares the self-hosted fonts in the stylesheet', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    preg_match_all("#url\\(/fonts/([a-z0-9-]+\\.woff2)\\)#", $css, $files);

    expect(array_values(array_unique($files[1])))->toBe([
        'archivo-latin-ext.woff2', 'archivo-latin.woff2',
        'jetbrains-mono-latin-ext.woff2', 'jetbrains-mono-latin.woff2',
    ])->and($css)->not->toContain('googleapis');

    foreach ($files[1] as $file) {
        expect(dirname(__DIR__, 2)."/assets/fonts/{$file}")->toBeFile();
    }
});

it('has a page for addresses that do not exist', function () {
    $doc = pages($this)['404.html'];

    expect($doc->querySelector('title')->textContent)->toBe('phplives.dev: page not found')
        ->and(texts($doc, 'h1'))->toBe(['Lost parcel'])
        ->and(texts($doc, '.head p:first-of-type'))->toBe(['No page lives at this address. The link may be wrong, or the page may have moved.'])
        ->and(hrefs($doc, '.head a'))->toBe(['/', '/archive/'])
        ->and($doc->querySelector('meta[name="robots"]')?->getAttribute('content'))->toBe('noindex')
        ->and($doc->querySelector('link[rel="canonical"]'))->toBeNull()
        ->and(hrefs($doc, 'footer a'))->toContain('/privacy/', '/credits/');
});

it('lets search engines index every real page', function (string $page) {
    expect(pages($this)[$page]->querySelector('meta[name="robots"]'))->toBeNull();
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html']);

it('says "Newest first" once on the main page', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    expect(substr_count(pages($this)['index.html']->body->textContent, 'Newest first'))->toBe(1);
});

it('names who makes every credited item', function () {
    $doc = pages($this)['credits/index.html'];

    expect(count($doc->querySelectorAll('.credits .cr-from')))->toBe(count($doc->querySelectorAll('.credits .item')))
        ->and(texts($doc, '.credits .cr-from'))->toContain('VoidZero and the Vitest contributors');
});

it('opens every link that leaves the site in a new tab', function (string $page) {
    writeEntry($this->dir, '2026-09-01-pest-5.php', ['makers' => [['name' => 'Nuno Maduro', 'github' => 'nunomaduro']]]);
    writeEntry($this->dir, '2026-02-01-old.php');
    $links = iterator_to_array(pages($this, ['nunomaduro'], [new Post('https://dev-null.sh/@albertoarena/commits/c1', 'See https://phplives.dev', new DateTimeImmutable('2026-10-16'))])[$page]->querySelectorAll('a[href]'));

    $external = array_filter($links, fn ($a) => str_starts_with($a->getAttribute('href'), 'https://'));
    $internal = array_filter($links, fn ($a) => ! str_starts_with($a->getAttribute('href'), 'https://'));

    expect($external)->not->toBeEmpty();

    foreach ($external as $a) {
        expect($a->getAttribute('target'))->toBe('_blank', $a->getAttribute('href'))
            ->and(explode(' ', (string) $a->getAttribute('rel')))->toContain('noopener');
    }

    foreach ($internal as $a) {
        expect($a->hasAttribute('target'))->toBeFalse($a->getAttribute('href'));
    }
})->with(['index.html', 'archive/index.html', 'privacy/index.html', 'credits/index.html', 'feeds/index.html', '404.html']);

it('marks every shipped date in a form that tools can read', function () {
    writeEntry($this->dir, '2026-08-01-jellydash.php', ['name' => 'Jellydash']);
    writeEntry($this->dir, '2026-03-01-archived.php', ['name' => 'Archived']);

    $pages = pages($this);
    $times = fn (HTMLDocument $doc, string $selector) => array_map(
        fn ($node) => [$node->getAttribute('datetime'), $node->textContent],
        iterator_to_array($doc->querySelectorAll($selector)),
    );

    expect($times($pages['index.html'], '#manifest .lab .date time'))->toBe([['2026-08-01', '01/08/2026']])
        ->and($times($pages['index.html'], '#manifest table .date time'))->toBe([['2026-08-01', '01/08/2026']])
        ->and($times($pages['index.html'], '.slip dd time'))->toBe([['2026-08-01', '01/08/2026']])
        ->and($times($pages['archive/index.html'], 'table .date time'))->toBe([['2026-03-01', '01/03/2026']]);
});

it('puts the manifest tools on their own row, with the view toggle at the end', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    expect($css)->toContain('.sec-head{display:flex;flex-wrap:wrap;')
        ->and($css)->toMatch('/\.sec-tools\{[^}]*flex-basis:100%/')
        ->and($css)->toMatch('/\.sec-tools \.view\{[^}]*margin-left:auto/');
});

it('rings the whole filter group when its dropdown has focus, not the dropdown inside it', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    expect($css)->toContain('.filter:has(select:focus-visible){outline:2px solid var(--on-field);outline-offset:2px}')
        ->and($css)->toContain('.filter select:focus-visible{outline:0}')
        ->and($css)->toContain('.filter select:hover{background:var(--line)}');
});

it('lines up the blocks of the labels in a row', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    expect($css)->toContain('.bundle-new .grid{grid-template-rows:repeat(4,auto);row-gap:0}')
        ->and($css)->toContain('.bundle-new .grid>.lab{display:grid;grid-template-rows:subgrid;grid-row:span 4;container-type:normal}')
        ->and($css)->toContain('.bundle-new .lab>.ship{container:ship / inline-size}')
        ->and($css)->toContain('@container ship (max-width:341px){.waves{display:none}}');
});

it('prints the newest parcel strip inside the From block, so it does not push the other labels down', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    $strip = pages($this)['index.html']->querySelector('.lab.newest .blk.from > .lab-new');

    expect($strip)->not->toBeNull()
        ->and(trim($strip->textContent))->toBe('Newest parcel');
});

it('lets a long track link break only after a slash', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    $link = pages($this)['index.html']->querySelector('.lab .track a .mono');

    expect($link->querySelectorAll('wbr'))->toHaveCount(2)
        ->and($link->textContent)->toBe('github.com/pestphp/pest');
});

it('puts the track link under its caption on every label', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    expect($css)->toContain('.lab .track{display:grid;justify-items:start;gap:2px}')
        ->and($css)->not->toContain('word-break:break-all');
});

it('keeps the track arrow next to the link text, and the label foot at the bottom of its block', function () {
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    expect($css)->toContain('.lab .track a{display:inline;line-height:1.6}')
        ->and($css)->toContain('.lab .track a svg{display:inline;vertical-align:-2px;margin-left:6px}')
        ->and($css)->toContain('.bundle-new .lab>.blk:last-child{display:flex;flex-direction:column}')
        ->and($css)->toContain('.bundle-new .lab-foot{margin-top:auto;padding-top:6px}');
});

it('keeps the last part of a track link and its arrow on one line', function () {
    writeEntry($this->dir, '2026-09-01-pest-5.php');

    $tail = pages($this)['index.html']->querySelector('.lab .track a .tail');
    $css = file_get_contents(dirname(__DIR__, 2).'/assets/site.css');

    expect($tail->textContent)->toBe('pest')
        ->and($tail->querySelector('svg'))->not->toBeNull()
        ->and($css)->toContain('.lab .track .tail{white-space:nowrap}');
});
