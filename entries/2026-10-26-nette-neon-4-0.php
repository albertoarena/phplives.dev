<?php

return [
    'name' => 'Nette NEON 4.0',
    'sentence' => 'A new major version of NEON, a human-friendly data format simpler than YAML, that can now edit a file and change only what you edit.',
    'details' => 'NEON 4 parses a file into a lossless syntax tree, where every comment and every space has its place. Edits change only the tokens they touch, so a diff shows only your change. It requires PHP 8.4 or later.',
    'makers' => [
        ['name' => 'Nette', 'github' => 'nette'],
        ['name' => 'David Grudl', 'github' => 'dg'],
    ],
    'shipped' => '2026-10-26',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/nette/neon/releases/tag/v4.0.0',
    ],
    'source' => 'https://github.com/nette/neon',
    'package' => 'nette/neon',
    'licence' => 'BSD-3-Clause',
    'docs' => 'https://doc.nette.org/en/neon',
    'stack' => 'php',
    'topics' => ['devtools'],
    'devnull' => 'Shipped with PHP: Nette NEON 4.0. A new major version of NEON, a human-friendly data format simpler than YAML, that can now edit a file and change only what you edit. https://github.com/nette/neon'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-28',
    'added' => '2026-10-28',
];
