<?php

declare(strict_types=1);

use Dom\HTMLDocument;

function placeholder(): HTMLDocument
{
    return HTMLDocument::createFromFile(dirname(__DIR__, 2).'/placeholder/index.html', LIBXML_NOERROR);
}

it('quotes the line exactly', function () {
    expect(trim(preg_replace('/\s+/', ' ', placeholder()->querySelector('h1')->textContent)))
        ->toBe('PHP delivers. PHP lives!');
});

it('is a complete page on its own', function () {
    $doc = placeholder();

    expect($doc->documentElement->getAttribute('lang'))->toBe('en')
        ->and($doc->querySelector('meta[charset]'))->not->toBeNull()
        ->and($doc->querySelector('meta[name="viewport"]'))->not->toBeNull()
        ->and($doc->querySelector('title')->textContent)->toStartWith('phplives.dev')
        ->and($doc->querySelectorAll('script'))->toHaveCount(0)
        ->and($doc->querySelectorAll('link[rel="stylesheet"]:not([href^="https://fonts.googleapis.com/"])'))->toHaveCount(0);
});

it('follows the device theme', function () {
    expect(placeholder()->querySelector('style')->textContent)->toContain('prefers-color-scheme:dark');
});

it('only links out over HTTPS, in a new tab', function () {
    $links = iterator_to_array(placeholder()->querySelectorAll('a[href]'));

    expect($links)->not->toBeEmpty();

    foreach ($links as $link) {
        expect($link->getAttribute('href'))->toStartWith('https://')
            ->and($link->getAttribute('target'))->toBe('_blank')
            ->and($link->getAttribute('rel'))->toContain('noopener');
    }
});

it('does not link to whyphp.dev yet', function () {
    expect(file_get_contents(dirname(__DIR__, 2).'/placeholder/index.html'))->not->toContain('whyphp.dev');
});

it('tells Alberto\'s sites where a visit came from', function () {
    $hrefs = array_map(fn ($a) => $a->getAttribute('href'), iterator_to_array(placeholder()->querySelectorAll('a[href]')));

    expect($hrefs)->toContain(
        'https://albertoarena.it/?utm_source=phplives.dev&utm_medium=footer',
        'https://cogway.dev/?utm_source=phplives.dev&utm_medium=footer',
    );
});
