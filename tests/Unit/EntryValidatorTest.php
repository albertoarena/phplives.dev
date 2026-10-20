<?php

declare(strict_types=1);

use PhpLives\EntryValidator;

function errorsFor(array $entry, string $file = '2026-09-01-pest-5.php'): array
{
    return (new EntryValidator(new DateTimeImmutable('2026-10-16')))->errors($entry, $file);
}

it('accepts a valid entry', function () {
    expect(errorsFor(validEntry()))->toBe([]);
});

it('accepts an entry with more than one maker', function () {
    $entry = validEntry(['makers' => [
        ['name' => 'Nuno Maduro', 'github' => 'nunomaduro'],
        ['name' => 'Brent Roose', 'github' => 'brendt'],
    ]]);

    expect(errorsFor($entry))->toBe([]);
});

it('reports a missing field', function (string $field) {
    $entry = validEntry();
    unset($entry[$field]);

    expect(errorsFor($entry))->toContain("Missing field: {$field}.");
})->with(['name', 'sentence', 'makers', 'shipped', 'proof', 'source', 'stack', 'devnull', 'checked']);

it('reports an empty text field', function (string $field) {
    expect(errorsFor(validEntry([$field => '  '])))->toContain("Field {$field} is empty.");
})->with(['name', 'sentence', 'devnull']);

it('reports an unknown field', function () {
    expect(errorsFor(validEntry(['tagline' => 'x'])))->toContain('Unknown field: tagline.');
});

it('reports a bad date', function (string $field, string $value) {
    expect(errorsFor(validEntry([$field => $value])))
        ->toContain("Field {$field} is not a valid YYYY-MM-DD date.");
})->with([
    ['shipped', '01/09/2026'],
    ['shipped', '2026-02-30'],
    ['checked', '2026-13-01'],
    ['checked', 'yesterday'],
]);

it('reports a date in the future', function (string $field) {
    $entry = validEntry(['shipped' => '2026-09-01', 'checked' => '2026-10-16', $field => '2026-10-17']);

    expect(errorsFor($entry, '2026-10-17-pest-5.php'))->toContain("Field {$field} is in the future.");
})->with(['shipped', 'checked']);

it('reports a check made before the ship date', function () {
    $entry = validEntry(['checked' => '2026-08-31']);

    expect(errorsFor($entry))->toContain('Field checked is before the shipped date.');
});

it('reports an unknown stack', function () {
    expect(errorsFor(validEntry(['stack' => 'wordpress'])))
        ->toContain('Unknown stack: wordpress. Use one of: laravel, symfony, nativephp, php.');
});

it('reports a sentence that is too long', function () {
    $sentence = str_repeat('a', EntryValidator::MAX_SENTENCE + 1);

    expect(errorsFor(validEntry(['sentence' => $sentence])))
        ->toContain('The sentence is longer than '.EntryValidator::MAX_SENTENCE.' characters.');
});

it('counts characters, not bytes, in the sentence', function () {
    $sentence = str_repeat('é', EntryValidator::MAX_SENTENCE);

    expect(errorsFor(validEntry(['sentence' => $sentence])))->toBe([]);
});

it('reports an em dash in any text field', function (string $field) {
    expect(errorsFor(validEntry([$field => "Fast \u{2014} and small."])))
        ->toContain("Field {$field} has an em dash.");
})->with(['name', 'sentence', 'devnull']);

it('reports a link that is not HTTPS', function () {
    expect(errorsFor(validEntry(['source' => 'http://github.com/pestphp/pest'])))
        ->toContain('Field source is not an HTTPS link.');
});

it('reports a proof link that is not HTTPS', function () {
    $entry = validEntry(['proof' => ['kind' => 'release', 'url' => 'ftp://example.com']]);

    expect(errorsFor($entry))->toContain('Field proof.url is not an HTTPS link.');
});

it('reports an unknown proof kind', function () {
    $entry = validEntry(['proof' => ['kind' => 'repo-created', 'url' => 'https://github.com/pestphp/pest']]);

    expect(errorsFor($entry))->toContain('Unknown proof kind: repo-created. Use one of: release, tag, post.');
});

it('reports a malformed proof', function () {
    expect(errorsFor(validEntry(['proof' => 'https://github.com'])))
        ->toContain('Field proof needs a kind and a url.');
});

it('reports an entry with no makers', function () {
    expect(errorsFor(validEntry(['makers' => []])))->toContain('Field makers needs at least one maker.');
});

it('reports a maker without a name', function () {
    $entry = validEntry(['makers' => [['github' => 'nunomaduro']]]);

    expect(errorsFor($entry))->toContain('Maker 1 needs a name.');
});

it('reports a bad GitHub username', function (string $github) {
    $entry = validEntry(['makers' => [['name' => 'Nuno Maduro', 'github' => $github]]]);

    expect(errorsFor($entry))->toContain("Maker 1 has a bad GitHub username: {$github}.");
})->with(['@nunomaduro', 'https://github.com/nunomaduro', '-nuno', 'nuno--maduro', '']);

it('allows a maker without a GitHub username', function () {
    $entry = validEntry(['makers' => [['name' => 'coolLabs']]]);

    expect(errorsFor($entry))->toBe([]);
});

it('reports a file name that does not match the ship date', function () {
    expect(errorsFor(validEntry(), '2026-09-02-pest-5.php'))
        ->toContain('The file name date does not match the shipped date.');
});

it('reports a badly formed file name', function (string $file) {
    expect(errorsFor(validEntry(), $file))
        ->toContain('The file name must be YYYY-MM-DD-slug.php, with a lower-case slug.');
})->with(['pest-5.php', '2026-09-01-Pest_5.php', '2026-09-01-.php', '2026-09-01-pest-5.txt']);
