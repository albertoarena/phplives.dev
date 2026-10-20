<?php

declare(strict_types=1);

namespace PhpLives;

use GdImage;

/**
 * The drawing tools behind ShareCard, on top of GD.
 *
 * It draws at SCALE times the final size and scales down once at the end, so text
 * and edges come out smooth. Positions and sizes are in final card pixels. GD fills
 * the inside of each shape. PHP only shades the edges of rounded corners, rings and
 * waves, from the exact distance to the edge.
 *
 * @internal
 */
final class ShareCanvas
{
    public const int SCALE = 2;

    /** The cap height of Archivo, in em. */
    public const float CAP = 0.686;

    private GdImage $im;

    /** @var array<string, float> */
    private static array $advances = [];

    public function __construct(private readonly int $width, private readonly int $height, int $field)
    {
        $this->im = imagecreatetruecolor($width * self::SCALE, $height * self::SCALE);
        imagealphablending($this->im, true);
        imagefilledrectangle($this->im, 0, 0, $width * self::SCALE - 1, $height * self::SCALE - 1, $field);
    }

    /**
     * The final image as PNG bytes.
     */
    public function png(): string
    {
        $out = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($out, $this->im, 0, 0, 0, 0, $this->width, $this->height, imagesx($this->im), imagesy($this->im));

        ob_start();
        imagepng($out, null, 6);

        return ob_get_clean();
    }

    // ---- shapes ---------------------------------------------------------------

    /**
     * A soft shadow on the plain field, for a sheet of paper that is drawn over it.
     * It is the exact Gaussian blur of a box, which splits into one curve across and
     * one down. It is worked out on a small image, then scaled up by GD.
     */
    public function shadow(float $x, float $y, float $w, float $h, float $offsetY, float $blur, float $spread, int $field, int $ink, float $alpha): void
    {
        $k = 8; // card pixels for each pixel of the small image
        $sigma = $blur / 2;
        $x0 = $x - $spread;
        $x1 = $x + $w + $spread;
        $y0 = $y + $offsetY - $spread;
        $y1 = $y + $h + $offsetY + $spread;

        // Only the part around the box: the field further out does not change.
        $reach = 4 * $sigma;
        $left = max(0, (int) floor(($x0 - $reach) / $k));
        $top = max(0, (int) floor(($y0 - $reach) / $k));
        $right = min((int) ceil($this->width / $k), (int) ceil(($x1 + $reach) / $k));
        $bottom = min((int) ceil($this->height / $k), (int) ceil(($y1 + $reach) / $k));

        $across = fn (float $at, float $from, float $to): float => self::normal(($at - $from) / $sigma) - self::normal(($at - $to) / $sigma);
        $small = imagecreatetruecolor($right - $left, $bottom - $top);

        for ($py = $top; $py < $bottom; $py++) {
            $down = $across(($py + 0.5) * $k, $y0, $y1) * $alpha;

            for ($px = $left; $px < $right; $px++) {
                imagesetpixel($small, $px - $left, $py - $top, self::mix($field, $ink, $across(($px + 0.5) * $k, $x0, $x1) * $down));
            }
        }

        imagesetinterpolation($small, IMG_BILINEAR_FIXED);
        $big = imagescale($small, imagesx($small) * $k * self::SCALE, imagesy($small) * $k * self::SCALE);
        imagecopy($this->im, $big, $left * $k * self::SCALE, $top * $k * self::SCALE, 0, 0, imagesx($big), imagesy($big));
    }

    /**
     * The normal distribution up to z, with the erf of Abramowitz and Stegun (7.1.26).
     */
    private static function normal(float $z): float
    {
        $x = abs($z) / M_SQRT2;
        $t = 1 / (1 + 0.3275911 * $x);
        $erf = 1 - ((((1.061405429 * $t - 1.453152027) * $t + 1.421413741) * $t - 0.284496736) * $t + 0.254829592) * $t * exp(-$x * $x);

        return $z >= 0 ? (1 + $erf) / 2 : (1 - $erf) / 2;
    }

    /**
     * A rectangle with rounded corners. GD fills it, and only the corners are shaded here.
     */
    public function roundRect(float $x, float $y, float $w, float $h, float $r, int $color): void
    {
        $s = self::SCALE;
        [$X0, $Y0, $X1, $Y1, $R] = [(int) round($x * $s), (int) round($y * $s), (int) round(($x + $w) * $s), (int) round(($y + $h) * $s), (int) round($r * $s)];

        imagefilledrectangle($this->im, $X0 + $R, $Y0, $X1 - $R - 1, $Y1 - 1, $color);
        imagefilledrectangle($this->im, $X0, $Y0 + $R, $X0 + $R - 1, $Y1 - $R - 1, $color);
        imagefilledrectangle($this->im, $X1 - $R, $Y0 + $R, $X1 - 1, $Y1 - $R - 1, $color);

        foreach ([[$X0, $Y0, $X0 + $R, $Y0 + $R], [$X1 - $R, $Y0, $X1 - $R, $Y0 + $R], [$X0, $Y1 - $R, $X0 + $R, $Y1 - $R], [$X1 - $R, $Y1 - $R, $X1 - $R, $Y1 - $R]] as [$cx0, $cy0, $ccx, $ccy]) {
            for ($Y = $cy0; $Y < $cy0 + $R; $Y++) {
                for ($X = $cx0; $X < $cx0 + $R; $X++) {
                    $d = sqrt(($X + 0.5 - $ccx) ** 2 + ($Y + 0.5 - $ccy) ** 2) - $R;
                    $this->blend($X, $Y, $color, min(1.0, max(0.0, 0.5 - $d)));
                }
            }
        }
    }

    /** A straight print rule. */
    public function rule(float $x, float $y, float $w, float $h, int $color): void
    {
        $s = self::SCALE;
        imagefilledrectangle($this->im, (int) round($x * $s), (int) round($y * $s), (int) round(($x + $w) * $s) - 1, (int) round(($y + $h) * $s) - 1, $color);
    }

    /**
     * Wave lines, the shape of the label's SVG waves (q7.5 -6 15 0 t15 0): a sine.
     * Each column shades only the few pixels around the line.
     */
    public function waves(float $x, float $y, float $w, int $lines, float $gap, float $period, float $amp, float $stroke, int $color): void
    {
        $s = self::SCALE;
        $k = 2 * M_PI / ($period * $s);
        $A = $amp * $s;
        $half = $stroke * $s / 2;
        $X0 = (int) round($x * $s);
        $X1 = (int) round(($x + $w) * $s);

        for ($i = 0; $i < $lines; $i++) {
            $yc = ($y + $i * $gap) * $s;

            for ($X = $X0; $X < $X1; $X++) {
                $t = $X + 0.5 - $X0;
                $f = $yc - $A * sin($k * $t);
                $slope = sqrt(1 + ($A * $k * cos($k * $t)) ** 2);
                $reach = $half * $slope + 1;

                for ($Y = (int) floor($f - $reach); $Y <= (int) ceil($f + $reach); $Y++) {
                    $d = abs($Y + 0.5 - $f) / $slope - $half;
                    $this->blend($X, $Y, $color, min(1.0, max(0.0, 0.5 - $d)));
                }
            }
        }
    }

    // ---- text -------------------------------------------------------------------

    /**
     * The advance of a text run, in device pixels. GD takes points at 96 dpi, so 1px is 0.75pt.
     * GD reads no GPOS kerning, so there is none.
     */
    public static function advance(string $font, float $devicePx, string $text): float
    {
        if ($text === '') {
            return 0.0;
        }

        $key = "{$font}|{$devicePx}|{$text}";

        if (! isset(self::$advances[$key])) {
            // The right edge of "text|", less the right edge of "|", is the advance of the text.
            $with = imagettfbbox($devicePx * 0.75, 0, $font, $text.'|');
            $bar = imagettfbbox($devicePx * 0.75, 0, $font, '|');
            self::$advances[$key] = (float) ($with[2] - $bar[2]);
        }

        return self::$advances[$key];
    }

    /**
     * The width of a text run in card pixels. Tracking and word spacing are in em.
     */
    public function width(string $font, float $px, string $text, float $tracking = 0.0, float $wordSpace = 0.0): float
    {
        $dp = $px * self::SCALE;
        $width = 0.0;
        $units = $this->units($text, $tracking);

        foreach ($units as $unit) {
            $width += self::advance($font, $dp, $unit) + ($unit === ' ' ? $wordSpace * $dp : 0);
        }

        return ($width + $tracking * $dp * (count($units) - 1)) / self::SCALE;
    }

    /**
     * Draws a text run from the start of its baseline. Words, or letters when it is
     * tracked, are set one by one, so word spacing and tracking work.
     */
    public function text(string $font, float $px, float $x, float $y, string $text, int $color, float $tracking = 0.0, float $wordSpace = 0.0): void
    {
        $dp = $px * self::SCALE;
        $X = $x * self::SCALE;

        foreach ($this->units($text, $tracking) as $unit) {
            if ($unit !== ' ') {
                imagettftext($this->im, $dp * 0.75, 0, (int) round($X), (int) round($y * self::SCALE), $color, $font, $unit);
            }

            $X += self::advance($font, $dp, $unit) + ($unit === ' ' ? $wordSpace * $dp : 0) + $tracking * $dp;
        }
    }

    /**
     * The largest size at which a text fits in a width, on one or two lines. One line
     * wins when it fits at $preferOne of the size that two lines would get. Below
     * $minPx it keeps getting smaller, so it never runs past the width.
     *
     * @return array{size: float, lines: list<string>}
     */
    public function fit(string $font, string $text, float $maxWidth, float $maxPx, float $minPx, float $wordSpace, float $preferOne): array
    {
        $words = explode(' ', $text);
        $two = null;

        for ($px = $maxPx; $px >= 6; $px--) {
            if ($this->width($font, $px, $text, 0, $wordSpace) <= $maxWidth) {
                return $two !== null && $px < $two['size'] * $preferOne ? $two : ['size' => $px, 'lines' => [$text]];
            }

            if ($two === null && $px >= $minPx && count($words) > 1) {
                $lines = $this->balance($font, $px, $words, $wordSpace);

                if (max(array_map(fn (string $line) => $this->width($font, $px, $line, 0, $wordSpace), $lines)) <= $maxWidth) {
                    $two = ['size' => $px, 'lines' => $lines];
                }
            }

            if ($two !== null && $px < $minPx) {
                return $two;
            }
        }

        return $two ?? ['size' => 6.0, 'lines' => [$text]];
    }

    /**
     * The largest size, from $maxPx down, at which one line of text fits.
     */
    public function shrink(string $font, string $text, float $maxWidth, float $maxPx, float $tracking = 0.0): float
    {
        for ($px = $maxPx; $px > 6; $px -= 0.5) {
            if ($this->width($font, $px, $text, $tracking) <= $maxWidth) {
                return $px;
            }
        }

        return 6.0;
    }

    /**
     * The break into two lines with the shortest longer line.
     *
     * @param  list<string>  $words
     * @return list<string>
     */
    private function balance(string $font, float $px, array $words, float $wordSpace): array
    {
        $best = null;

        for ($i = 1; $i < count($words); $i++) {
            $lines = [implode(' ', array_slice($words, 0, $i)), implode(' ', array_slice($words, $i))];
            $longest = max($this->width($font, $px, $lines[0], 0, $wordSpace), $this->width($font, $px, $lines[1], 0, $wordSpace));

            if ($best === null || $longest < $best[0]) {
                $best = [$longest, $lines];
            }
        }

        return $best[1];
    }

    /**
     * @return list<string> Letters when the text is tracked, words and spaces when it is not.
     */
    private function units(string $text, float $tracking): array
    {
        return $tracking != 0.0 ? mb_str_split($text) : preg_split('/( )/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    // ---- the postmark -------------------------------------------------------------

    /**
     * The ring postmark of the labels, scaled from its 120-unit SVG. It is drawn on a
     * clear layer, then laid on the card with GD's multiply effect, like ink on paper.
     * The whole stamp is turned $turn degrees counter-clockwise.
     *
     * @param  list<string>  $middle  The mono lines in the middle, such as ['14/09', '2026'].
     */
    public function postmark(float $cx, float $cy, float $diameter, array $middle, int $ink, float $turn, float $opacity, string $ringFont, string $monoFont): void
    {
        $u = $diameter / 120 * self::SCALE; // device pixels in one SVG unit
        $size = (int) ceil(2 * 57.5 * $u) + 8;
        $c = $size / 2;
        $layer = imagecreatetruecolor($size, $size);
        imagealphablending($layer, false);
        imagefilledrectangle($layer, 0, 0, $size - 1, $size - 1, 0x7F000000);
        imagealphablending($layer, true);
        $color = (((int) round(127 * (1 - $opacity))) << 24) | $ink;

        $this->ring($layer, $c, 56 * $u, 3 * $u, $ink, $opacity);
        $this->ring($layer, $c, 34 * $u, 1.6 * $u, $ink, $opacity);

        $fontPx = 11.5 * $u;
        $this->arcText($layer, $c, 'PHPLIVES.DEV', $ringFont, $fontPx, 42 * $u, 2.2 * $u, true, $turn, $color);
        $this->arcText($layer, $c, 'SHIPPED', $ringFont, $fontPx, 46 * $u, 2.6 * $u, false, $turn, $color);

        // The SVG sets the two lines at y 58 and 73 of 120: baselines 2 above and 13 below the middle.
        foreach ($middle as $i => $line) {
            $advance = self::advance($monoFont, 12 * $u, $line);
            [$bx, $by] = self::turn(0, ($i === 0 ? -2 : 13) * $u, $turn);
            [$ox, $oy] = self::turn(-$advance / 2, 0, $turn);
            imagettftext($layer, 12 * $u * 0.75, $turn, (int) round($c + $bx + $ox), (int) round($c + $by + $oy), $color, $monoFont, $line);
        }

        imagelayereffect($this->im, IMG_EFFECT_MULTIPLY);
        imagecopy($this->im, $layer, (int) round($cx * self::SCALE - $c), (int) round($cy * self::SCALE - $c), 0, 0, $size, $size);
        imagelayereffect($this->im, IMG_EFFECT_ALPHABLEND);
    }

    /**
     * A ring on a clear layer. Each row shades only the pixels near the ring.
     */
    private function ring(GdImage $layer, float $c, float $radius, float $stroke, int $ink, float $opacity): void
    {
        $outer = $radius + $stroke / 2 + 1;
        $inner = $radius - $stroke / 2 - 1;

        for ($Y = (int) floor($c - $outer); $Y <= (int) ceil($c + $outer); $Y++) {
            $dy = $Y + 0.5 - $c;

            if (abs($dy) > $outer) {
                continue;
            }

            $xo = sqrt($outer ** 2 - $dy ** 2);
            $xi = abs($dy) < $inner ? sqrt($inner ** 2 - $dy ** 2) : 0.0;

            foreach ([[-$xo, -$xi], [$xi, $xo]] as [$from, $to]) {
                for ($X = (int) floor($c + $from); $X <= (int) ceil($c + $to); $X++) {
                    $d = abs(sqrt(($X + 0.5 - $c) ** 2 + $dy ** 2) - $radius) - $stroke / 2;
                    $cover = min(1.0, max(0.0, 0.5 - $d)) * $opacity;

                    if ($cover > 0) {
                        imagesetpixel($layer, $X, $Y, (((int) round(127 * (1 - $cover))) << 24) | $ink);
                    }
                }
            }
        }
    }

    /**
     * Sets a word around the ring, one letter at a time: over the top, or along the
     * bottom with the letters upright.
     */
    private function arcText(GdImage $layer, float $c, string $text, string $font, float $px, float $base, float $tracking, bool $top, float $turn, int $color): void
    {
        $letters = mb_str_split($text);
        $advances = array_map(fn (string $letter) => self::advance($font, $px, $letter), $letters);
        $total = array_sum($advances) + $tracking * (count($letters) - 1);
        // Spread the letters along the middle of their height, so the gaps look even.
        $middle = $top ? $base + self::CAP * $px / 2 : $base - self::CAP * $px / 2;
        $along = -$total / 2;

        foreach ($letters as $i => $letter) {
            $angle = rad2deg(($along + $advances[$i] / 2) / $middle);
            $deg = $top ? $turn - $angle : $turn + $angle;
            [$bx, $by] = self::turn(0, $top ? -$base : $base, $deg);
            [$ox, $oy] = self::turn(-$advances[$i] / 2, 0, $deg);
            imagettftext($layer, $px * 0.75, $deg, (int) round($c + $bx + $ox), (int) round($c + $by + $oy), $color, $font, $letter);
            $along += $advances[$i] + $tracking;
        }
    }

    /**
     * Turns a vector counter-clockwise on screen, where y points down.
     *
     * @return array{0: float, 1: float}
     */
    private static function turn(float $x, float $y, float $deg): array
    {
        $a = deg2rad($deg);

        return [$x * cos($a) + $y * sin($a), -$x * sin($a) + $y * cos($a)];
    }

    // ---- pixels -------------------------------------------------------------------

    /** Moves one device pixel towards a colour by $alpha. */
    private function blend(int $x, int $y, int $color, float $alpha): void
    {
        if ($alpha <= 0.0 || $x < 0 || $y < 0 || $x >= imagesx($this->im) || $y >= imagesy($this->im)) {
            return;
        }

        imagesetpixel($this->im, $x, $y, self::mix(imagecolorat($this->im, $x, $y), $color, $alpha));
    }

    /** A colour between two colours. */
    private static function mix(int $from, int $to, float $t): int
    {
        $t = min(1.0, max(0.0, $t));
        $channel = fn (int $shift) => (int) round((($from >> $shift) & 0xFF) + ((($to >> $shift) & 0xFF) - (($from >> $shift) & 0xFF)) * $t);

        return ($channel(16) << 16) | ($channel(8) << 8) | $channel(0);
    }
}
