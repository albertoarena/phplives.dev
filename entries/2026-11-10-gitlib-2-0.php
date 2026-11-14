<?php

return [
    'name' => 'gitlib 2.0',
    'sentence' => 'A new major version of gitlib, a PHP library for working with Git repositories, where every git operation now goes through a typed driver.',
    'details' => 'The driver returns typed hashes, enums and value objects instead of raw command output. A failing operation always throws an exception. Dates are DateTimeImmutable, and names in diffs and trees are UTF-8. It needs PHP 8.4 or later, and an upgrade guide lists every change.',
    'makers' => [
        ['name' => 'Gitonomy', 'github' => 'gitonomy'],
    ],
    'shipped' => '2026-11-10',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/gitonomy/gitlib/releases/tag/v2.0.0',
    ],
    'source' => 'https://github.com/gitonomy/gitlib',
    'package' => 'gitonomy/gitlib',
    'licence' => 'MIT',
    'docs' => 'https://gitonomy.github.io/gitlib/',
    'stack' => 'php',
    'topics' => ['devtools'],
    'devnull' => 'Shipped with PHP: gitlib 2.0. A new major version of gitlib, a PHP library for working with Git repositories, where every git operation now goes through a typed driver. https://github.com/gitonomy/gitlib'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-14',
    'added' => '2026-11-14',
];
