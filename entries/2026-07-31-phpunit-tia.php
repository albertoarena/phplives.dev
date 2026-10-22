<?php

return [
    'name' => 'PHPUnit TIA',
    'sentence' => 'A PHPUnit extension for Test Impact Analysis (TIA), which runs only the tests that relate to the files you changed.',
    'details' => 'It is a port of the TIA engine from Pest. Tests that your change does not affect are marked as skipped. It needs PHPUnit 13, PHP 8.4 and a coverage driver (pcov or Xdebug).',
    'makers' => [
        ['name' => 'Jason McCreary', 'github' => 'jasonmccreary'],
    ],
    'shipped' => '2026-07-31',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/jasonmccreary/phpunit-tia/releases/tag/v0.1.0',
    ],
    'source' => 'https://github.com/jasonmccreary/phpunit-tia',
    'package' => 'jasonmccreary/phpunit-tia',
    'licence' => 'MIT',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: PHPUnit TIA. A PHPUnit extension for Test Impact Analysis (TIA), which runs only the tests that relate to the files you changed. https://github.com/jasonmccreary/phpunit-tia'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-22',
];
