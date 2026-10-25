<?php

return [
    'name' => 'PHPMD 3.0',
    'sentence' => 'A new major version of PHPMD, which finds possible problems in PHP code, with a setup wizard and YAML configuration.',
    'details' => 'phpmd init sets up a project, and phpmd migrate turns an XML config into YAML. A #[SuppressWarnings] attribute hides a warning, and --threads runs the analysis in parallel. It needs PHP 8.1 or later.',
    'makers' => [
        ['name' => 'PHPMD', 'github' => 'phpmd'],
    ],
    'shipped' => '2026-10-04',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/phpmd/phpmd/releases/tag/3.0.0',
    ],
    'source' => 'https://github.com/phpmd/phpmd',
    'package' => 'phpmd/phpmd',
    'licence' => 'BSD-3-Clause',
    'docs' => 'https://phpmd.org/documentation/index.html',
    'stack' => 'php',
    'topics' => ['devtools'],
    'devnull' => 'Shipped with PHP: PHPMD 3.0. A new major version of PHPMD, which finds possible problems in PHP code, with a setup wizard and YAML configuration. https://github.com/phpmd/phpmd'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-21',
];
