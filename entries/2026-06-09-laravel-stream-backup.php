<?php

return [
    'name' => 'laravel-stream-backup',
    'sentence' => 'A Laravel package that streams database backups to S3, SFTP or local disk, with the same small memory use whatever the size of the database.',
    'details' => 'The dump is piped through compression and optional encryption, with no database-sized file on disk. Restores stream back the same way, for a whole database or single tables, with rollback on failure. It supports MySQL, PostgreSQL and SQLite.',
    'makers' => [
        ['name' => 'Ahmed Nour', 'github' => 'ahmed-nour-dev'],
    ],
    'shipped' => '2026-06-09',
    'proof' => [
        'kind' => 'tag',
        'url' => 'https://github.com/ahmed-nour-dev/laravel-stream-backup/tree/v1.0.0',
    ],
    'source' => 'https://github.com/ahmed-nour-dev/laravel-stream-backup',
    'package' => 'ahmednour/laravel-stream-backup',
    'licence' => 'MIT',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: laravel-stream-backup. A Laravel package that streams database backups to S3, SFTP or local disk, with the same small memory use whatever the size of the database. https://github.com/ahmed-nour-dev/laravel-stream-backup'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-21',
];
