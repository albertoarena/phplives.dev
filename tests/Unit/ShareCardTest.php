<?php

declare(strict_types=1);

use PhpLives\Entry;
use PhpLives\ShareCard;

/**
 * An entry for the card tests. Pass changes to the entry file's fields.
 */
function cardEntry(array $changes = [], string $slug = 'pest-5'): Entry
{
    return Entry::fromArray($slug, validEntry($changes));
}

/**
 * Makers for an entry file, from their names.
 */
function makersNamed(string ...$names): array
{
    return array_map(fn (string $name) => ['name' => $name, 'github' => null], $names);
}

it('needs GD with FreeType and PNG support', function (array $gd, bool $available) {
    expect(ShareCard::available($gd))->toBe($available);
})->with([
    'no GD' => [[], false],
    'no FreeType' => [['FreeType Support' => false, 'PNG Support' => true], false],
    'no PNG' => [['FreeType Support' => true, 'PNG Support' => false], false],
    'both' => [['FreeType Support' => true, 'PNG Support' => true], true],
]);

it('names one maker, two makers, or the lead and a count', function (array $names, string $line) {
    expect(ShareCard::makers(cardEntry(['makers' => makersNamed(...$names)])))->toBe($line);
})->with([
    [['Laravel'], 'Laravel'],
    [['Nuno Maduro', 'Brent Roose'], 'Nuno Maduro and Brent Roose'],
    [['Nuno Maduro', 'Márk Magyar', 'Matthieu Napoli', 'Brent Roose', 'Pauline Vos'], 'Nuno Maduro and 4 more'],
]);

it('describes the card for people who cannot see it', function () {
    $entry = cardEntry(['name' => 'Laravel Vet', 'makers' => makersNamed('Laravel'), 'shipped' => '2026-09-14']);

    expect(ShareCard::alt($entry))->toBe('Shipping label for Laravel Vet by Laravel, shipped on 14/09/2026. phplives.dev');
});

it('lives next to the entry page, with a version that changes with what the card shows', function () {
    $entry = cardEntry(slug: 'laravel-vet');
    $url = ShareCard::url($entry);

    expect(ShareCard::path($entry))->toBe('/entries/laravel-vet/card.png')
        ->and($url)->toMatch('#^https://phplives\.dev/entries/laravel-vet/card\.png\?v=[0-9a-f]{8}$#')
        ->and(ShareCard::url(cardEntry(slug: 'laravel-vet')))->toBe($url)
        ->and(ShareCard::url(cardEntry(['name' => 'Other'], 'laravel-vet')))->not->toBe($url)
        ->and(ShareCard::url(cardEntry(['makers' => makersNamed('Someone Else')], 'laravel-vet')))->not->toBe($url)
        ->and(ShareCard::url(cardEntry(['shipped' => '2026-09-02'], 'laravel-vet')))->not->toBe($url)
        ->and(ShareCard::url(cardEntry(['stack' => 'laravel'], 'laravel-vet')))->not->toBe($url)
        ->and(ShareCard::url(cardEntry(['sentence' => 'Another sentence.'], 'laravel-vet')))->toBe($url);
});

it('keeps the label, and all it says, inside the square in the middle', function (array $changes) {
    $layout = (new ShareCard())->layout(cardEntry($changes));
    $label = $layout['label'];

    expect($label['x'])->toBeGreaterThanOrEqual(ShareCard::SQUARE_LEFT)
        ->and($label['x'] + $label['w'])->toBeLessThanOrEqual(ShareCard::SQUARE_LEFT + ShareCard::HEIGHT)
        ->and($label['y'])->toBeGreaterThanOrEqual(0)
        ->and($label['y'] + $label['h'])->toBeLessThanOrEqual(ShareCard::HEIGHT)
        ->and(count($layout['name']['lines']))->toBeLessThanOrEqual(2)
        ->and(max($layout['name']['widths']))->toBeLessThanOrEqual($layout['name']['max'])
        ->and($layout['makers']['width'])->toBeLessThanOrEqual($layout['makers']['max']);
})->with([
    'short' => [['name' => 'Vet']],
    'long name' => [['name' => 'Symfony Security Auditor']],
    'very long name' => [['name' => 'The Extremely Long Name Of A Project That Keeps Going And Going']],
    'one very long word' => [['name' => 'Supercalifragilisticexpialidocious-framework-bundle']],
    'many makers' => [['makers' => makersNamed('Nuno Maduro', 'Márk Magyar', 'Matthieu Napoli', 'Brent Roose', 'Pauline Vos')]],
    'long maker' => [['makers' => makersNamed('Gianfrancesco Aurecchia Bartolomeo Della Valle Superiore')]],
])->skip(! ShareCard::available(), 'GD with FreeType is not installed.');

it('sets a short name on one line and a long one on two', function () {
    $card = new ShareCard();

    expect($card->layout(cardEntry(['name' => 'Laravel Vet']))['name']['lines'])->toBe(['Laravel Vet'])
        ->and($card->layout(cardEntry(['name' => 'WooCommerce 11.0']))['name']['lines'])->toBe(['WooCommerce 11.0'])
        ->and($card->layout(cardEntry(['name' => 'Symfony Security Auditor']))['name']['lines'])->toBe(['Symfony', 'Security Auditor']);
})->skip(! ShareCard::available(), 'GD with FreeType is not installed.');

it('grows the label for a name on two lines', function () {
    $card = new ShareCard();
    $one = $card->layout(cardEntry(['name' => 'Laravel Vet']))['label'];
    $two = $card->layout(cardEntry(['name' => 'Symfony Security Auditor']))['label'];

    expect($two['h'])->toBeGreaterThan($one['h'])
        ->and($two['y'])->toBeLessThan($one['y']);
})->skip(! ShareCard::available(), 'GD with FreeType is not installed.');

describe('the image', function () {
    beforeEach(function () {
        if (! ShareCard::available()) {
            $this->markTestSkipped('GD with FreeType is not installed.');
        }
    });

    it('is a 1200x630 PNG', function () {
        $size = getimagesizefromstring((new ShareCard())->png(cardEntry()));

        expect($size[0])->toBe(1200)
            ->and($size[1])->toBe(630)
            ->and($size['mime'])->toBe('image/png');
    });

    it('comes out the same for the same entry', function () {
        $entry = cardEntry(['name' => 'Symfony Security Auditor']);

        expect(md5((new ShareCard())->png($entry)))->toBe(md5((new ShareCard())->png($entry)));
    });

    it('differs from one entry to the next', function () {
        $card = new ShareCard();

        expect(md5($card->png(cardEntry(['name' => 'Laravel Vet']))))->not->toBe(md5($card->png(cardEntry(['name' => 'Pest 5']))));
    });

    it('prints the label on the Prussian field, with no orange', function () {
        $im = imagecreatefromstring((new ShareCard())->png(cardEntry()));
        $layout = (new ShareCard())->layout(cardEntry());
        $label = $layout['label'];
        $orange = 0;

        for ($y = 0; $y < 630; $y += 3) {
            for ($x = 0; $x < 1200; $x += 3) {
                $c = imagecolorat($im, $x, $y);
                [$r, $g, $b] = [($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF];
                $orange += (int) ($r > 200 && $g > 60 && $g < 130 && $b < 80);
            }
        }

        expect(imagecolorat($im, 2, 2))->toBe(0x12285A)
            ->and(imagecolorat($im, (int) ($label['x'] + 20), (int) ($label['y'] + 8)))->toBe(0xFFFFFF)
            ->and($orange)->toBe(0);
    });

    // A card takes about 0.13 s. The limit leaves room for a slow CI runner, and still catches a real slowdown.
    it('draws a card in under 3 seconds', function () {
        $card = new ShareCard();
        $card->png(cardEntry());
        $start = hrtime(true);
        $card->png(cardEntry(['name' => 'Symfony Security Auditor']));

        expect((hrtime(true) - $start) / 1e9)->toBeLessThan(3.0);
    });
});
