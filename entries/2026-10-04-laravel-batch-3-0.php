<?php

return [
    'name' => 'Laravel Batch 3.0',
    'sentence' => 'A new major version of Laravel Batch, which inserts and updates many rows in one query, now with every value sent as a bound parameter.',
    'details' => 'Laravel\'s grammar now quotes every column and table name. Large batches are split to fit the database\'s parameter limit and run in one transaction. Every method works on MySQL, MariaDB, PostgreSQL and SQLite.',
    'makers' => [
        ['name' => 'Mohammad Ghanbari', 'github' => 'mavinoo'],
    ],
    'shipped' => '2026-10-04',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/mavinoo/laravelBatch/releases/tag/v3.0.0',
    ],
    'source' => 'https://github.com/mavinoo/laravelBatch',
    'package' => 'mavinoo/laravel-batch',
    'licence' => 'MIT',
    'docs' => 'https://github.com/mavinoo/laravelBatch/blob/v3.0.0/UPGRADE.md',
    'stack' => 'laravel',
    'topics' => ['database'],
    'devnull' => 'Shipped with PHP: Laravel Batch 3.0. A new major version of Laravel Batch, which inserts and updates many rows in one query, now with every value sent as a bound parameter. https://github.com/mavinoo/laravelBatch'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-10',
    'added' => '2026-11-10',
];
