<?php

declare(strict_types=1);

namespace PhpLives;

/**
 * The share card of one entry: the image that shows when someone shares its page.
 *
 * It is the entry's shipping label, 1200x630, drawn with GD. Some sites, such as
 * /dev/null, crop the card to the square in the middle (630x630), so everything that
 * says which entry it is sits in that square. The waves and the line on the sides are
 * only decoration. A shared card is kept by other sites for good, so it never uses
 * Signal Orange: the newest parcel will not stay the newest.
 */
final class ShareCard
{
    public const int WIDTH = 1200;

    public const int HEIGHT = 630;

    /** The left edge of the square in the middle. */
    public const int SQUARE_LEFT = (self::WIDTH - self::HEIGHT) / 2;

    /**
     * Part of the version in each card's address. Change it when the drawing changes,
     * so sites fetch the new cards.
     */
    public const int VERSION = 1;

    private const string FONTS = __DIR__.'/../assets/fonts/card';

    private const string NAME_FONT = self::FONTS.'/archivo-72-850.ttf';

    private const string ROUTE_FONT = self::FONTS.'/archivo-62-900.ttf';

    private const string RING_FONT = self::FONTS.'/archivo-100-800.ttf';

    private const string BOLD_FONT = self::FONTS.'/archivo-100-700.ttf';

    private const string MONO_FONT = self::FONTS.'/jetbrainsmono-600.ttf';

    private const int PRUSSIAN_INK = 0x12285A;

    private const int THERMAL_WHITE = 0xFFFFFF;

    private const int PRINT_BLACK = 0x121212;

    private const int PRINT_GREY = 0x4A4A46;

    private const int BAND_TEXT = 0xF4F7F2;

    private const int BAND_MUTED_INK = 0xBCCBE8;

    /** The waves on the blue field, as on the site's own share card. */
    private const int FIELD_WAVES = 0x4A67A8;

    private const int SHADOW = 0x050B1C;

    // The label, in card pixels.
    private const int LABEL_X = 307;

    private const int LABEL_W = 586;

    private const int PAD = 26;

    private const int FROM_H = 122;

    private const int SHIPPED_H = 126;

    private const int FOOT_H = 56;

    private const int RULE = 3;

    private const int ROUTE_W = 124;

    /**
     * True when PHP can draw the cards: GD with FreeType and PNG support.
     *
     * @param  array<string, mixed>|null  $gd  What gd_info() says. Null to ask GD.
     */
    public static function available(?array $gd = null): bool
    {
        $gd ??= function_exists('gd_info') ? gd_info() : [];

        return ($gd['FreeType Support'] ?? false) === true && ($gd['PNG Support'] ?? false) === true;
    }

    /**
     * The makers as the card names them: one, two, or the lead and how many more.
     */
    public static function makers(Entry $entry): string
    {
        $names = array_map(fn (Maker $maker) => $maker->name, $entry->makers);

        return match (count($names)) {
            1 => $names[0],
            2 => "{$names[0]} and {$names[1]}",
            default => $names[0].' and '.(count($names) - 1).' more',
        };
    }

    /**
     * The text for people who cannot see the card.
     */
    public static function alt(Entry $entry): string
    {
        return "Shipping label for {$entry->name} by ".self::makers($entry).', shipped on '.Format::date($entry->shipped).'. phplives.dev';
    }

    /**
     * Where the card is, from the site's root: next to the entry's page.
     */
    public static function path(Entry $entry): string
    {
        return Site::entryPath($entry).'card.png';
    }

    /**
     * The card's full address. Its version changes when anything on the card changes,
     * so sites that keep old cards fetch the new one.
     */
    public static function url(Entry $entry): string
    {
        $shown = implode("\n", [self::VERSION, $entry->name, self::makers($entry), Format::date($entry->shipped), $entry->stack]);

        return Site::URL.self::path($entry).'?v='.substr(sha1($shown), 0, 8);
    }

    /**
     * Where the label and its text go, in card pixels. The label grows with a name on
     * two lines, and stays in the middle.
     *
     * @return array{
     *     label: array{x: float, y: float, w: float, h: float},
     *     name: array{size: float, lines: list<string>, widths: list<float>, max: float},
     *     makers: array{text: string, size: float, width: float, max: float},
     *     contents: float,
     * }
     */
    public function layout(Entry $entry): array
    {
        $canvas = new ShareCanvas(1, 1, 0);
        $inner = self::LABEL_W - 2 * self::PAD;
        $name = $canvas->fit(self::NAME_FONT, $entry->name, $inner, 88, 44, 0.12, 0.72);
        $nameHeight = $name['size'] * 0.95 * (count($name['lines']) - 1) + $name['size'] * 0.74;
        $contents = 34 + 18 + $nameHeight + 26;
        $height = self::FROM_H + $contents + self::SHIPPED_H + self::FOOT_H + 3 * self::RULE;
        $makers = self::makers($entry);
        $makersMax = $inner - self::ROUTE_W - 20;
        $makersSize = $canvas->shrink(self::BOLD_FONT, $makers, $makersMax, 30);

        return [
            'label' => ['x' => self::LABEL_X, 'y' => (self::HEIGHT - $height) / 2, 'w' => self::LABEL_W, 'h' => $height],
            'name' => $name + [
                'widths' => array_map(fn (string $line) => $canvas->width(self::NAME_FONT, $name['size'], $line, 0, 0.12), $name['lines']),
                'max' => $inner,
            ],
            'makers' => ['text' => $makers, 'size' => $makersSize, 'width' => $canvas->width(self::BOLD_FONT, $makersSize, $makers), 'max' => $makersMax],
            'contents' => $contents,
        ];
    }

    /**
     * The card as PNG bytes. The same entry always gives the same bytes.
     */
    public function png(Entry $entry): string
    {
        $layout = $this->layout($entry);
        ['x' => $x, 'y' => $y, 'w' => $w, 'h' => $h] = $layout['label'];
        $pad = self::PAD;
        $c = new ShareCanvas(self::WIDTH, self::HEIGHT, self::PRUSSIAN_INK);

        $c->shadow($x, $y, $w, $h, 22, 44, -10, self::PRUSSIAN_INK, self::SHADOW, 0.75);

        // Outside the square: waves on the left, the site and its line on the right.
        $c->waves(44, 270, 200, 3, 45, 50, 5.2, 4, self::FIELD_WAVES);
        $c->text(self::MONO_FONT, 19, 950, 262, 'phplives.dev', self::BAND_MUTED_INK, 0.02);
        $c->text(self::NAME_FONT, 34, 950, 312, 'PHP delivers.', self::BAND_TEXT, 0, 0.12);
        $c->text(self::NAME_FONT, 34, 950, 350, 'PHP lives!', self::BAND_TEXT, 0, 0.12);

        $c->roundRect($x, $y, $w, $h, 6, self::THERMAL_WHITE);

        // FROM, with the route code on the right.
        $c->text(self::BOLD_FONT, 13, $x + $pad, $y + 34, 'FROM', self::PRINT_GREY, 0.09);
        $c->text(self::BOLD_FONT, $layout['makers']['size'], $x + $pad, $y + 80, $layout['makers']['text'], self::PRINT_BLACK);
        $this->route($c, $x + $w - $pad - self::ROUTE_W, $y + 16, self::ROUTE_W, self::FROM_H - 32, Format::stackCode($entry->stack), Format::stackLabel($entry->stack));
        $top = $y + self::FROM_H;
        $c->rule($x, $top, $w, self::RULE, self::PRINT_BLACK);
        $top += self::RULE;

        // CONTENTS: the name.
        $c->text(self::BOLD_FONT, 13, $x + $pad, $top + 34, 'CONTENTS', self::PRINT_GREY, 0.09);
        $size = $layout['name']['size'];

        foreach ($layout['name']['lines'] as $i => $line) {
            $c->text(self::NAME_FONT, $size, $x + $pad, $top + 34 + 18 + $size * 0.74 + $i * $size * 0.95, $line, self::PRINT_BLACK, 0, 0.12);
        }

        $top += $layout['contents'];
        $c->rule($x, $top, $w, self::RULE, self::PRINT_BLACK);
        $top += self::RULE;

        // SHIPPED: the date, the waves and the postmark over the label's corner.
        $date = Format::date($entry->shipped);
        $c->text(self::BOLD_FONT, 13, $x + $pad, $top + 34, 'SHIPPED', self::PRINT_GREY, 0.09);
        $c->text(self::MONO_FONT, 52, $x + $pad, $top + 100, $date, self::PRINT_BLACK, -0.01);
        $dateEnd = $x + $pad + $c->width(self::MONO_FONT, 52, $date, -0.01);
        $stamp = 186;
        $stampX = $x + $w - 66;
        $stampY = $top + self::SHIPPED_H / 2 + 26;
        $c->waves($dateEnd + 14, $top + 52, $stampX - $stamp / 2 - $dateEnd - 4, 3, 17, 30, 3, 2.8, self::PRUSSIAN_INK);
        $top += self::SHIPPED_H;
        $c->rule($x, $top, $w, self::RULE, self::PRINT_BLACK);
        $top += self::RULE;

        // The foot: where the parcel is listed.
        $c->text(self::MONO_FONT, 21, $x + $pad, $top + 36, 'phplives.dev', self::PRINT_BLACK);

        $c->postmark($stampX, $stampY, $stamp, [$entry->shipped->format('d/m'), $entry->shipped->format('Y')], self::PRUSSIAN_INK, 9, 0.9, self::RING_FONT, self::MONO_FONT);

        return $c->png();
    }

    /**
     * The route code block: the stack code in white on black, the stack's name below it.
     */
    private function route(ShareCanvas $c, float $x, float $y, float $w, float $h, string $code, string $label): void
    {
        $codePx = 62;
        $labelPx = $codePx * 0.24;
        $c->roundRect($x, $y, $w, $h, 3, self::PRINT_BLACK);
        $codeWidth = $c->width(self::ROUTE_FONT, $codePx, $code, 0.01);
        $c->text(self::ROUTE_FONT, $codePx, $x + ($w - $codeWidth) / 2, $y + $h / 2 + $codePx * ShareCanvas::CAP / 2 - $labelPx * 0.55, $code, self::THERMAL_WHITE, 0.01);
        $size = $c->shrink(self::BOLD_FONT, $label, $w - 12, $labelPx);
        $c->text(self::BOLD_FONT, $size, $x + ($w - $c->width(self::BOLD_FONT, $size, $label)) / 2, $y + $h - $labelPx * 0.62, $label, self::THERMAL_WHITE);
    }
}
