<?php

declare(strict_types=1);

use chillerlan\QRCode\QRCode as Reader;
use chillerlan\QRCode\QROptions;
use PhpLives\Entry;
use PhpLives\QrCode;

/**
 * An entry for the QR code tests.
 */
function qrEntry(string $slug = 'vichuploaderbundle-3-0'): Entry
{
    return Entry::fromArray($slug, validEntry(['name' => 'VichUploaderBundle 3.0']));
}

/**
 * The text a QR code image holds, read back as a phone camera would.
 */
function readQr(string $png): string
{
    return (new Reader(new QROptions(['readerUseImagickIfAvailable' => false])))->readFromBlob($png)->data;
}

/**
 * The dark squares an SVG path draws, as "x,y" keys, in modules.
 *
 * @return list<string>
 */
function qrSquares(string $path, int $module, int $offset = 0): array
{
    preg_match_all('/M(\d+) (\d+)h'.$module.'v'.$module.'h-'.$module.'z/', $path, $squares, PREG_SET_ORDER);

    return array_map(fn (array $s) => (((int) $s[1] - $offset) / $module).','.(((int) $s[2] - $offset) / $module), $squares);
}

/**
 * The dark modules of a QR code, as "x,y" keys.
 *
 * @return list<string>
 */
function qrDark(QrCode $qr): array
{
    $dark = [];

    foreach (range(0, $qr->size() - 1) as $y) {
        foreach (range(0, $qr->size() - 1) as $x) {
            if ($qr->dark($x, $y)) {
                $dark[] = "{$x},{$y}";
            }
        }
    }

    return $dark;
}

it('keeps the QR code files next to the entry page', function () {
    expect(QrCode::svgPath(qrEntry()))->toBe('/entries/vichuploaderbundle-3-0/qr.svg')
        ->and(QrCode::pngPath(qrEntry()))->toBe('/entries/vichuploaderbundle-3-0/qr.png');
});

it('names the downloaded files after the entry', function () {
    expect(QrCode::fileName(qrEntry(), 'svg'))->toBe('phplives-vichuploaderbundle-3-0-qr.svg')
        ->and(QrCode::fileName(qrEntry(), 'png'))->toBe('phplives-vichuploaderbundle-3-0-qr.png');
});

it('needs GD with PNG support for the PNG file', function (array $gd, bool $available) {
    expect(QrCode::pngAvailable($gd))->toBe($available);
})->with([
    'no GD' => [[], false],
    'no PNG' => [['PNG Support' => false], false],
    'PNG' => [['PNG Support' => true], true],
]);

it('stays small enough for a phone to read on the label, even for the longest slug', function () {
    // 33 modules is version 4. At 3px a module, it is 99px wide.
    expect(QrCode::forEntry(qrEntry())->size())->toBe(33)
        ->and(QrCode::forEntry(qrEntry('sqlite-database-integration-3-0'))->size())->toBe(33);
});

it('draws one square for each dark module on the label', function () {
    $qr = QrCode::forEntry(qrEntry());

    expect(qrSquares($qr->path(3), 3))->toEqualCanonicalizing(qrDark($qr));
});

it('opens the entry page when the PNG file is scanned', function () {
    expect(readQr(QrCode::png(qrEntry())))->toBe('https://phplives.dev/entries/vichuploaderbundle-3-0/');
})->skip(! QrCode::pngAvailable(), 'GD has no PNG support');

it('makes the PNG file a large square for print', function () {
    $size = getimagesizefromstring(QrCode::png(qrEntry()));

    expect($size['mime'])->toBe('image/png')
        ->and($size[0])->toBe($size[1])
        ->and($size[0])->toBeGreaterThanOrEqual(900)->toBeLessThanOrEqual(1100);
})->skip(! QrCode::pngAvailable(), 'GD has no PNG support');

it('makes the SVG file a valid image with a title and the same squares', function () {
    $qr = QrCode::forEntry(qrEntry());
    $svg = simplexml_load_string(QrCode::svg(qrEntry()));
    $svg->registerXPathNamespace('s', 'http://www.w3.org/2000/svg');
    $path = (string) $svg->xpath('//s:path')[0]['d'];

    expect($svg->getName())->toBe('svg')
        ->and((string) $svg['role'])->toBe('img')
        ->and((string) $svg->title)->toBe('QR code for VichUploaderBundle 3.0 on phplives.dev')
        // A quiet zone of 4 modules on each side, as the QR standard asks.
        ->and((string) $svg['viewBox'])->toBe('0 0 41 41')
        ->and(qrSquares($path, 1, 4))->toEqualCanonicalizing(qrDark($qr));
});

it('prints the files in black on white only, so every scanner reads them', function () {
    preg_match_all('/#[0-9a-f]{6}\b/i', QrCode::svg(qrEntry()), $colours);

    expect(array_unique(array_map(strtolower(...), $colours[0])))->toEqualCanonicalizing(['#121212', '#ffffff']);
});
