<?php

declare(strict_types=1);

use PhpLives\Entries;
use PhpLives\Markdown;

/**
 * The README badge: one image for every entry, at /badge.svg.
 */
function badgeSvg(): string
{
    return (string) file_get_contents(dirname(__DIR__, 2).'/art/badge.svg');
}

it('keeps the badge in the art folder', function () {
    expect(dirname(__DIR__, 2).'/art/badge.svg')->toBeFile();
});

it('draws the badge as valid SVG with a title for screen readers', function () {
    $svg = simplexml_load_string(badgeSvg());

    expect($svg)->not->toBeFalse()
        ->and($svg->getName())->toBe('svg')
        ->and((string) $svg['role'])->toBe('img')
        ->and((string) $svg['aria-label'])->toBe('Featured on phplives.dev')
        ->and((string) $svg->title)->toBe('Featured on phplives.dev');
});

it('draws the badge text as paths, so it needs no fonts', function () {
    $svg = badgeSvg();

    expect($svg)->not->toContain('<text')
        ->not->toContain('font-family')
        ->not->toContain('@font-face')
        ->and(substr_count($svg, '<path'))->toBeGreaterThan(0);
});

it('keeps the badge about 20px high and small', function () {
    $svg = simplexml_load_string(badgeSvg());

    expect((int) $svg['height'])->toBe(20)
        ->and((int) $svg['width'])->toBeGreaterThan(60)
        ->and(strlen(badgeSvg()))->toBeLessThan(8 * 1024);
});

it('gives the badge no Signal Orange, because it stays in a README for good', function () {
    $colours = [];
    preg_match_all('/#[0-9a-f]{6}\b|#[0-9a-f]{3}\b/i', badgeSvg(), $colours);

    expect($colours[0])->toContain('#12285a')
        ->and(array_map(strtolower(...), $colours[0]))->each->toBeIn(['#12285a', '#121212', '#ffffff', '#fff'])
        ->and(badgeSvg())->not->toContain('orange');
});

it('writes the Markdown for the badge of an entry, linked to its page', function () {
    $dir = sys_get_temp_dir().'/phplives-badge-'.bin2hex(random_bytes(4));
    mkdir($dir);
    writeEntry($dir, '2026-09-01-pest-5.php');
    $entry = Entries::load($dir, new DateTimeImmutable('2026-10-20'))->all()[0];
    array_map(unlink(...), glob($dir.'/*'));
    rmdir($dir);

    expect(Markdown::badge($entry))
        ->toBe('[![Featured on phplives.dev](https://phplives.dev/badge.svg)](https://phplives.dev/entries/pest-5/)');
});
