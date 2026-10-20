<?php

declare(strict_types=1);

namespace PhpLives;

use DateTimeImmutable;

/**
 * Checks one entry file against the rules in CONTRIBUTING.md.
 * The build fails if any entry has errors.
 */
final class EntryValidator
{
    public const int MAX_SENTENCE = 160;

    public const int MAX_DETAILS = 400;

    public const array FIELDS = [
        'name', 'sentence', 'makers', 'shipped', 'proof', 'source', 'stack', 'devnull', 'checked', 'added',
    ];

    /** Fields an entry may leave out. The entry page shows each one only when it is given. */
    public const array OPTIONAL_FIELDS = ['details', 'package', 'licence', 'docs'];

    public const array STACKS = ['laravel', 'symfony', 'nativephp', 'wordpress', 'php'];

    public const array PROOF_KINDS = ['release', 'tag', 'post'];

    private const array TEXT_FIELDS = ['name', 'sentence', 'devnull', 'details'];

    private const string EM_DASH = "\u{2014}";

    private const string GITHUB_USERNAME = '/^[A-Za-z0-9](?:[A-Za-z0-9]|-(?=[A-Za-z0-9])){0,38}$/';

    /** Packagist's own rule for a package name. */
    private const string PACKAGE = '#^[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9](([_.]?|-{0,2})[a-z0-9]+)*$#';

    /** A short SPDX licence id, such as MIT or Apache-2.0. */
    private const string LICENCE = '/^[A-Za-z0-9][A-Za-z0-9.+-]{0,39}$/';

    private const string FILE_NAME = '/^(\d{4}-\d{2}-\d{2})-[a-z0-9]+(?:-[a-z0-9]+)*\.php$/';

    public function __construct(private readonly DateTimeImmutable $today) {}

    /**
     * @return list<string> One message per problem. Empty when the entry is valid.
     */
    public function errors(array $entry, string $fileName): array
    {
        $errors = [];

        foreach (self::FIELDS as $field) {
            if (! array_key_exists($field, $entry)) {
                $errors[] = "Missing field: {$field}.";
            }
        }

        foreach (array_diff(array_keys($entry), self::FIELDS, self::OPTIONAL_FIELDS) as $field) {
            $errors[] = "Unknown field: {$field}.";
        }

        foreach (self::TEXT_FIELDS as $field) {
            if (isset($entry[$field])) {
                array_push($errors, ...$this->textErrors($field, $entry[$field]));
            }
        }

        if (isset($entry['sentence']) && is_string($entry['sentence'])
            && mb_strlen(trim($entry['sentence'])) > self::MAX_SENTENCE) {
            $errors[] = 'The sentence is longer than '.self::MAX_SENTENCE.' characters.';
        }

        if (isset($entry['details']) && is_string($entry['details'])
            && mb_strlen(trim($entry['details'])) > self::MAX_DETAILS) {
            $errors[] = 'The details are longer than '.self::MAX_DETAILS.' characters.';
        }

        if (array_key_exists('package', $entry)
            && ! (is_string($entry['package']) && preg_match(self::PACKAGE, $entry['package']))) {
            $errors[] = 'Field package is not a Packagist name, such as vendor/name.';
        }

        if (array_key_exists('licence', $entry)
            && ! (is_string($entry['licence']) && preg_match(self::LICENCE, $entry['licence']))) {
            $errors[] = 'Field licence is not a short SPDX id, such as MIT.';
        }

        if (array_key_exists('docs', $entry) && ! $this->isHttps($entry['docs'])) {
            $errors[] = 'Field docs is not an HTTPS link.';
        }

        $shipped = isset($entry['shipped']) ? $this->date('shipped', $entry['shipped'], $errors) : null;
        $checked = isset($entry['checked']) ? $this->date('checked', $entry['checked'], $errors) : null;
        $added = isset($entry['added']) ? $this->date('added', $entry['added'], $errors) : null;

        if ($shipped && $checked && $checked < $shipped) {
            $errors[] = 'Field checked is before the shipped date.';
        }

        if ($shipped && $added && $added < $shipped) {
            $errors[] = 'Field added is before the shipped date.';
        }

        if (isset($entry['makers'])) {
            array_push($errors, ...$this->makerErrors($entry['makers']));
        }

        if (isset($entry['proof'])) {
            array_push($errors, ...$this->proofErrors($entry['proof']));
        }

        if (isset($entry['source']) && ! $this->isHttps($entry['source'])) {
            $errors[] = 'Field source is not an HTTPS link.';
        }

        if (isset($entry['stack']) && ! in_array($entry['stack'], self::STACKS, true)) {
            $errors[] = 'Unknown stack: '.(is_string($entry['stack']) ? $entry['stack'] : gettype($entry['stack']))
                .'. Use one of: '.implode(', ', self::STACKS).'.';
        }

        if (! preg_match(self::FILE_NAME, $fileName, $match)) {
            $errors[] = 'The file name must be YYYY-MM-DD-slug.php, with a lower-case slug.';
        } elseif ($shipped && $match[1] !== $shipped->format('Y-m-d')) {
            $errors[] = 'The file name date does not match the shipped date.';
        }

        return $errors;
    }

    /** @return list<string> */
    private function textErrors(string $field, mixed $value): array
    {
        if (! is_string($value)) {
            return ["Field {$field} must be text."];
        }

        if (trim($value) === '') {
            return ["Field {$field} is empty."];
        }

        if (str_contains($value, self::EM_DASH)) {
            return ["Field {$field} has an em dash."];
        }

        return [];
    }

    private function date(string $field, mixed $value, array &$errors): ?DateTimeImmutable
    {
        $date = is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;

        if ($date === false || $date->format('Y-m-d') !== $value) {
            $errors[] = "Field {$field} is not a valid YYYY-MM-DD date.";

            return null;
        }

        if ($date > $this->today->setTime(0, 0)) {
            $errors[] = "Field {$field} is in the future.";
        }

        return $date;
    }

    /** @return list<string> */
    private function makerErrors(mixed $makers): array
    {
        if (! is_array($makers) || $makers === []) {
            return ['Field makers needs at least one maker.'];
        }

        $errors = [];

        foreach (array_values($makers) as $index => $maker) {
            $number = $index + 1;

            if (! is_array($maker) || ! isset($maker['name']) || ! is_string($maker['name']) || trim($maker['name']) === '') {
                $errors[] = "Maker {$number} needs a name.";
            }

            if (is_array($maker) && array_key_exists('github', $maker)
                && ! (is_string($maker['github']) && preg_match(self::GITHUB_USERNAME, $maker['github']))) {
                $errors[] = "Maker {$number} has a bad GitHub username: ".(is_string($maker['github']) ? $maker['github'] : '').'.';
            }
        }

        return $errors;
    }

    /** @return list<string> */
    private function proofErrors(mixed $proof): array
    {
        if (! is_array($proof) || ! isset($proof['kind'], $proof['url'])) {
            return ['Field proof needs a kind and a url.'];
        }

        $errors = [];

        if (! in_array($proof['kind'], self::PROOF_KINDS, true)) {
            $errors[] = 'Unknown proof kind: '.(is_string($proof['kind']) ? $proof['kind'] : gettype($proof['kind']))
                .'. Use one of: '.implode(', ', self::PROOF_KINDS).'.';
        }

        if (! $this->isHttps($proof['url'])) {
            $errors[] = 'Field proof.url is not an HTTPS link.';
        }

        return $errors;
    }

    private function isHttps(mixed $url): bool
    {
        return is_string($url)
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && parse_url($url, PHP_URL_SCHEME) === 'https';
    }
}
