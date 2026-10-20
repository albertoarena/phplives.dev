<?php

declare(strict_types=1);

use PhpLives\Format;
use PhpLives\Maker;

it('writes dates as DD/MM/YYYY', function () {
    expect(Format::date(new DateTimeImmutable('2026-09-01')))->toBe('01/09/2026');
});

it('credits one maker by name', function () {
    expect(Format::credit([new Maker('Olivier', 'olivier-ls')]))->toBe('Olivier');
});

it('credits more makers after the lead', function (array $names, string $credit) {
    $makers = array_map(fn (string $name) => new Maker($name), $names);

    expect(Format::credit($makers))->toBe($credit);
})->with([
    [['Nuno Maduro', 'Brent Roose'], 'Nuno Maduro, with Brent Roose'],
    [['Nuno Maduro', 'Brent Roose', 'Matthieu Napoli'], 'Nuno Maduro, with Brent Roose and Matthieu Napoli'],
    [['A', 'B', 'C', 'D'], 'A, with B, C and D'],
]);

it('shows a link without its scheme or trailing slash', function (string $url, string $text) {
    expect(Format::linkText($url))->toBe($text);
})->with([
    ['https://github.com/laravel/ai', 'github.com/laravel/ai'],
    ['https://trussphp.com/', 'trussphp.com'],
    ['https://www.example.com/a/', 'www.example.com/a'],
    ['https://trussphp.com/?utm_source=phplives.dev&utm_medium=entry', 'trussphp.com'],
    ['https://example.com/a?b=c#d', 'example.com/a'],
]);

it('gives each stack a code and a label', function (string $stack, string $code, string $label) {
    expect(Format::stackCode($stack))->toBe($code)
        ->and(Format::stackLabel($stack))->toBe($label);
})->with([
    ['laravel', 'LRV', 'Laravel'],
    ['symfony', 'SYM', 'Symfony'],
    ['nativephp', 'NTV', 'NativePHP'],
    ['php', 'PHP', 'plain PHP'],
]);

it('names the kind of date proof', function (string $kind, string $label) {
    expect(Format::proofLabel($kind))->toBe($label);
})->with([
    ['release', 'release'],
    ['tag', 'version tag'],
    ['post', 'announcement'],
]);

it('draws a Code 39 barcode with start and stop marks', function () {
    $bars = Format::barcode('1');

    // "*1*": 3 characters of 9 elements (6 narrow, 3 wide), plus 2 narrow gaps.
    expect($bars)->toHaveCount(29)
        ->and($bars[0])->toBe(['width' => 1, 'ink' => true])
        ->and(array_sum(array_column($bars, 'width')))->toBe(3 * (6 * 1 + 3 * 2.5) + 2)
        ->and(array_values(array_unique(array_column($bars, 'width'))))->toEqualCanonicalizing([1, 2.5]);
});

it('only draws digits in a barcode', function () {
    Format::barcode('12a');
})->throws(InvalidArgumentException::class);
