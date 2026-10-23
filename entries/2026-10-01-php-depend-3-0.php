<?php

return [
    'name' => 'PHP Depend 3.0',
    'sentence' => 'A new major version of PHP Depend, a tool that shows how easy a PHP design is to extend, reuse and maintain.',
    'details' => 'It parses files in parallel, with one worker per CPU core. Its configuration moves from XML to YAML. It reads PHP 8.4 and 8.5 syntax. It needs PHP 8.1 or later on a 64 bit build.',
    'makers' => [
        ['name' => 'PHP Depend', 'github' => 'pdepend'],
    ],
    'shipped' => '2026-10-01',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/pdepend/pdepend/releases/tag/3.0.0',
    ],
    'source' => 'https://github.com/pdepend/pdepend',
    'package' => 'pdepend/pdepend',
    'licence' => 'BSD-3-Clause',
    'docs' => 'https://pdepend.org/',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: PHP Depend 3.0. A new major version of PHP Depend, a tool that shows how easy a PHP design is to extend, reuse and maintain. https://github.com/pdepend/pdepend'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-23',
    'added' => '2026-10-23',
];
