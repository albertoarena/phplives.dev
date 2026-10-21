<?php

return [
    'name' => 'Manticore',
    'sentence' => 'An ahead-of-time compiler for PHP, written in PHP, that turns PHP code into static binaries that run with no PHP runtime.',
    'details' => 'It compiles a large part of PHP 8.5 to arm64 and x86_64 binaries through LLVM. It can rebuild itself byte for byte. It runs on macOS and Linux.',
    'makers' => [
        ['name' => 'Taras Chornyi', 'github' => 'blacktrs'],
    ],
    'shipped' => '2026-07-21',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/manticorephp/compiler/releases/tag/v0.6.0',
    ],
    'source' => 'https://github.com/manticorephp/compiler',
    'package' => 'manticorephp/compiler',
    'licence' => 'MIT',
    'docs' => 'https://github.com/manticorephp/compiler/tree/main/docs',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Manticore. An ahead-of-time compiler for PHP, written in PHP, that turns PHP code into static binaries that run with no PHP runtime. https://github.com/manticorephp/compiler'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-21',
];
