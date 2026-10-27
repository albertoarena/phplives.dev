<?php

declare(strict_types=1);

namespace PhpLives;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode as Encoder;
use chillerlan\QRCode\QROptions;

/**
 * The QR code of an entry page. It sits on the entry's label, and makers can download
 * it as SVG or PNG to put the page on a slide, a poster or a flyer. The library
 * only encodes the link; the drawing is ours, so it prints in the label's ink.
 */
final class QrCode
{
    /** Print Black and white: the label's ink, on plain white so every scanner reads it. */
    private const string INK = '#121212';

    private const string PAPER = '#ffffff';

    /** The empty margin the QR standard asks for, in modules, on each side of a file. */
    private const int QUIET = 4;

    /** About how wide the PNG file is, in pixels. Big enough to print. */
    private const int PNG_SIZE = 1024;

    /**
     * @param  list<list<bool>>  $modules  The modules by row, true when dark.
     */
    private function __construct(private array $modules) {}

    public static function forEntry(Entry $entry): self
    {
        return self::for(Site::URL.Site::entryPath($entry));
    }

    public static function for(string $text): self
    {
        $matrix = (new Encoder(new QROptions(['eccLevel' => EccLevel::M, 'addQuietzone' => false])))
            ->addByteSegment($text)
            ->getQRMatrix();
        $size = $matrix->getSize();
        $modules = [];

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $modules[$y][$x] = $matrix->check($x, $y);
            }
        }

        return new self($modules);
    }

    /**
     * How many modules wide the code is, without the quiet zone.
     */
    public function size(): int
    {
        return count($this->modules);
    }

    public function dark(int $x, int $y): bool
    {
        return $this->modules[$y][$x];
    }

    /**
     * SVG path data with one square for each dark module, each $module units wide.
     * $offset moves every square right and down, to leave room for the quiet zone.
     */
    public function path(int $module, int $offset = 0): string
    {
        $d = '';

        foreach ($this->modules as $y => $row) {
            foreach ($row as $x => $dark) {
                if ($dark) {
                    $d .= 'M'.($x * $module + $offset).' '.($y * $module + $offset)."h{$module}v{$module}h-{$module}z";
                }
            }
        }

        return $d;
    }

    public static function svgPath(Entry $entry): string
    {
        return Site::entryPath($entry).'qr.svg';
    }

    public static function pngPath(Entry $entry): string
    {
        return Site::entryPath($entry).'qr.png';
    }

    /**
     * The name a downloaded file gets, so it is still clear in a downloads folder.
     */
    public static function fileName(Entry $entry, string $extension): string
    {
        return "phplives-{$entry->slug}-qr.{$extension}";
    }

    /**
     * True when GD can write the PNG file.
     *
     * @param  array<string, mixed>|null  $gd  What gd_info() returns. Null to ask GD.
     */
    public static function pngAvailable(?array $gd = null): bool
    {
        $gd ??= function_exists('gd_info') ? gd_info() : [];

        return ($gd['PNG Support'] ?? false) === true;
    }

    /**
     * The SVG file: one module is one unit, with the quiet zone around the code.
     */
    public static function svg(Entry $entry): string
    {
        $qr = self::forEntry($entry);
        $side = $qr->size() + 2 * self::QUIET;
        $title = htmlspecialchars("QR code for {$entry->name} on phplives.dev", ENT_XML1);

        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.($side * 8).'" height="'.($side * 8).'" viewBox="0 0 '.$side.' '.$side.'" shape-rendering="crispEdges" role="img" aria-label="'.$title.'">'
            ."<title>{$title}</title>"
            .'<rect width="'.$side.'" height="'.$side.'" fill="'.self::PAPER.'"/>'
            .'<path fill="'.self::INK.'" d="'.$qr->path(1, self::QUIET).'"/>'
            ."</svg>\n";
    }

    /**
     * The PNG file: about PNG_SIZE pixels wide, with whole pixels for each module.
     */
    public static function png(Entry $entry): string
    {
        $qr = self::forEntry($entry);
        $side = $qr->size() + 2 * self::QUIET;
        $scale = intdiv(self::PNG_SIZE, $side);
        $image = imagecreatetruecolor($side * $scale, $side * $scale);
        $ink = imagecolorallocate($image, 0x12, 0x12, 0x12);
        imagefill($image, 0, 0, imagecolorallocate($image, 0xff, 0xff, 0xff));

        for ($y = 0; $y < $qr->size(); $y++) {
            for ($x = 0; $x < $qr->size(); $x++) {
                if ($qr->dark($x, $y)) {
                    $left = ($x + self::QUIET) * $scale;
                    $top = ($y + self::QUIET) * $scale;
                    imagefilledrectangle($image, $left, $top, $left + $scale - 1, $top + $scale - 1, $ink);
                }
            }
        }

        ob_start();
        imagepng($image, null, 9);

        return (string) ob_get_clean();
    }
}
