<?php

return [
    'name' => 'ElephpantDB',
    'sentence' => 'A small SQL database in a single PHP file, which keeps its tables on local disk and takes SQL through a JSON-over-HTTP endpoint.',
    'details' => 'It supports CREATE TABLE, INSERT, SELECT, UPDATE, DELETE, indexes and VACUUM. It has file locking, checksums and crash recovery. It has no Composer dependencies. It needs PHP 8.3 or later on a 64-bit build.',
    'makers' => [
        ['name' => 'Anton Medvedev', 'github' => 'antonmedv'],
    ],
    'shipped' => '2026-08-30',
    'proof' => [
        'kind' => 'post',
        'url' => 'https://deployer.org/blog/elephpantdb',
    ],
    'source' => 'https://github.com/antonmedv/elephpantdb',
    'licence' => 'MIT',
    'stack' => 'php',
    'topics' => ['database'],
    'devnull' => 'Shipped with PHP: ElephpantDB. A small SQL database in a single PHP file, which keeps its tables on local disk and takes SQL through a JSON-over-HTTP endpoint. https://github.com/antonmedv/elephpantdb'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-22',
];
