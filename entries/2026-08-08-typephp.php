<?php

return [
    'name' => 'TypePHP',
    'sentence' => 'A pure PHP library that checks your PHPDoc types at runtime, including generics and array shapes, with no build step and no C extension.',
    'details' => 'You add it to an existing project with Composer, and it checks the types in your DocBlocks while the code runs. Strict mode throws a TypeError. Audit mode collects every type error into a JSON report without stopping the code.',
    'makers' => [
        ['name' => 'Reymart Calicdan', 'github' => 'rcalicdan'],
    ],
    'shipped' => '2026-08-08',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/typephp-php/typephp/releases/tag/v0.1.0',
    ],
    'source' => 'https://github.com/typephp-php/typephp',
    'package' => 'typephp/typephp',
    'licence' => 'MIT',
    'docs' => 'https://typephp.dev/',
    'stack' => 'php',
    'topics' => ['devtools'],
    'devnull' => 'Shipped with PHP: TypePHP. A pure PHP library that checks your PHPDoc types at runtime, including generics and array shapes, with no build step and no C extension. https://github.com/typephp-php/typephp'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-30',
    'added' => '2026-10-31',
];
