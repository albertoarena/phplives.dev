<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;

/**
 * One shipped project, read from an entry file in entries/.
 */
final readonly class Entry
{
    /**
     * @param  list<Maker>  $makers  The first maker is the lead.
     * @param  DateTimeImmutable  $added  The day the entry was added to the site.
     * @param  string|null  $details  A few sentences more, from the project's own source.
     * @param  string|null  $package  The Packagist name, such as vendor/name.
     * @param  string|null  $licence  A short SPDX licence id, such as MIT.
     * @param  string|null  $docs  The link to the project's docs.
     */
    public function __construct(
        public string $slug,
        public string $name,
        public string $sentence,
        public array $makers,
        public DateTimeImmutable $shipped,
        public string $proofKind,
        public string $proofUrl,
        public string $source,
        public string $stack,
        public string $devnull,
        public DateTimeImmutable $checked,
        public DateTimeImmutable $added,
        public ?string $details = null,
        public ?string $package = null,
        public ?string $licence = null,
        public ?string $docs = null,
    ) {}

    /**
     * Builds an entry from an array that EntryValidator has already accepted.
     */
    public static function fromArray(string $slug, array $data): self
    {
        return new self(
            slug: $slug,
            name: trim($data['name']),
            sentence: trim($data['sentence']),
            makers: array_map(
                fn (array $maker) => new Maker(trim($maker['name']), $maker['github'] ?? null),
                array_values($data['makers']),
            ),
            shipped: new DateTimeImmutable($data['shipped']),
            proofKind: $data['proof']['kind'],
            proofUrl: $data['proof']['url'],
            source: $data['source'],
            stack: $data['stack'],
            devnull: trim($data['devnull']),
            checked: new DateTimeImmutable($data['checked']),
            added: new DateTimeImmutable($data['added']),
            details: isset($data['details']) ? trim($data['details']) : null,
            package: $data['package'] ?? null,
            licence: $data['licence'] ?? null,
            docs: $data['docs'] ?? null,
        );
    }
}
