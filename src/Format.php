<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Turns entry data into the text the pages show.
 */
final class Format
{
    private const array STACKS = [
        'laravel' => ['LRV', 'Laravel'],
        'symfony' => ['SYM', 'Symfony'],
        'nativephp' => ['NTV', 'NativePHP'],
        'wordpress' => ['WRD', 'WordPress'],
        'php' => ['PHP', 'plain PHP'],
    ];

    private const array PROOFS = [
        'release' => 'release',
        'tag' => 'version tag',
        'post' => 'announcement',
    ];

    /** Code 39 patterns: n is a narrow element, w a wide one. Bars and gaps alternate. */
    private const array CODE39 = [
        '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw', '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn', '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn', '9' => 'nnwwnnwnn', '*' => 'nwnnwnwnn',
    ];

    public static function date(DateTimeImmutable $date): string
    {
        return $date->format('d/m/Y');
    }

    /**
     * "Lead", or "Lead, with B and C".
     *
     * @param  list<Maker>  $makers
     */
    public static function credit(array $makers): string
    {
        $names = array_map(fn (Maker $maker) => $maker->name, $makers);
        $lead = array_shift($names);

        if ($names === []) {
            return $lead;
        }

        $last = array_pop($names);

        return $lead.', with '.($names === [] ? $last : implode(', ', $names).' and '.$last);
    }

    /**
     * A link as people read it: no scheme, no query string or fragment, no trailing slash.
     */
    public static function linkText(string $url): string
    {
        return rtrim(preg_replace(['#^https?://#', '#[?\#].*$#'], '', $url), '/');
    }

    public static function stackCode(string $stack): string
    {
        return self::STACKS[$stack][0];
    }

    public static function stackLabel(string $stack): string
    {
        return self::STACKS[$stack][1];
    }

    public static function proofLabel(string $kind): string
    {
        return self::PROOFS[$kind];
    }

    /**
     * The bars of a Code 39 barcode for a number, with start and stop marks.
     *
     * @return list<array{width: int|float, ink: bool}>
     */
    public static function barcode(string $digits): array
    {
        if (! preg_match('/^\d+$/', $digits)) {
            throw new InvalidArgumentException("A barcode takes digits only: {$digits}");
        }

        $bars = [];
        $chars = str_split('*'.$digits.'*');

        foreach ($chars as $index => $char) {
            foreach (str_split(self::CODE39[$char]) as $position => $element) {
                $bars[] = ['width' => $element === 'w' ? 2.5 : 1, 'ink' => $position % 2 === 0];
            }

            if ($index < count($chars) - 1) {
                $bars[] = ['width' => 1, 'ink' => false];
            }
        }

        return $bars;
    }
}
