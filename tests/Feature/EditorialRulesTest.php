<?php

declare(strict_types=1);

/**
 * The tracked text files of the repo, as git sees them.
 *
 * @return list<string>
 */
function trackedTextFiles(): array
{
    $files = explode("\n", trim((string) shell_exec('git ls-files --cached --others --exclude-standard')));

    return array_values(array_filter(
        $files,
        fn (string $file): bool => $file !== ''
            && is_file($file)
            && preg_match('/\.(md|php|json|xml|yml|yaml|js|ts|css|html|txt)$/', $file) === 1,
    ));
}

it('has no em dashes in any text file', function () {
    $offenders = array_values(array_filter(
        trackedTextFiles(),
        fn (string $file): bool => str_contains((string) file_get_contents($file), "\u{2014}"),
    ));

    expect($offenders)->toBe([]);
});

it('finds the text files it checks', function () {
    expect(trackedTextFiles())->toContain('AGENTS.md', 'CONTRIBUTING.md');
});
