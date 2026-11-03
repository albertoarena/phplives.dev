<?php

return [
    'name' => 'Psalm 7.0',
    'sentence' => 'A new major version of Psalm, a static analysis tool that finds errors and security vulnerabilities in PHP code, with security analysis now on by default.',
    'details' => 'Type checking, security analysis and dead code detection now run together in one pass, and security analysis is up to 10 times faster. It tracks new kinds of injection, such as NoSQL and LLM prompts. It runs on 64 bit PHP 8.2 to 8.6.',
    'makers' => [
        ['name' => 'Daniil Gentili', 'github' => 'danog'],
        ['name' => 'Alies Lapatsin', 'github' => 'alies-dev'],
    ],
    'shipped' => '2026-11-02',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/vimeo/psalm/releases/tag/7.0.0',
    ],
    'source' => 'https://github.com/vimeo/psalm',
    'package' => 'vimeo/psalm',
    'licence' => 'MIT',
    'docs' => 'https://psalm.dev/docs/',
    'stack' => 'php',
    'topics' => ['devtools', 'security'],
    'devnull' => 'Shipped with PHP: Psalm 7.0. A new major version of Psalm, a static analysis tool that finds errors and security vulnerabilities in PHP code, with security analysis now on by default. https://github.com/vimeo/psalm'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-03',
    'added' => '2026-11-03',
];
