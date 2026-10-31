<?php

return [
    'name' => 'PHPStan Effect System',
    'sentence' => 'A PHPStan extension that lets you label methods with effects, such as slow, and stop chosen code from ever reaching them.',
    'details' => 'You declare an effect once, with an attribute on the method that has it. The extension follows the whole call graph, so the methods in between need no labels. An error shows the full chain of calls that reaches the effect.',
    'makers' => [
        ['name' => 'Dave Liddament', 'github' => 'DaveLiddament'],
    ],
    'shipped' => '2026-09-19',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/DaveLiddament/phpstan-effect-system/releases/tag/0.1.0',
    ],
    'source' => 'https://github.com/DaveLiddament/phpstan-effect-system',
    'package' => 'dave-liddament/phpstan-effect-system',
    'licence' => 'MIT',
    'stack' => 'php',
    'topics' => ['devtools'],
    'devnull' => 'Shipped with PHP: PHPStan Effect System. A PHPStan extension that lets you label methods with effects, such as slow, and stop chosen code from ever reaching them. https://github.com/DaveLiddament/phpstan-effect-system'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-30',
    'added' => '2026-10-31',
];
