<?php

return [
    'name' => 'Cadence',
    'sentence' => 'Model-based scheduling for Laravel: you attach cron or RRULE schedules to any Eloquent model, and Cadence fires events when they are due.',
    'details' => 'Its drivers cover cron and two RRULE libraries (php-rrule and Recurr). You can also write your own driver.',
    'makers' => [
        ['name' => 'Steve Bauman', 'github' => 'stevebauman'],
    ],
    'shipped' => '2026-05-04',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/DirectoryTree/Cadence/releases/tag/v1.0.0',
    ],
    'source' => 'https://github.com/DirectoryTree/Cadence',
    'package' => 'directorytree/cadence',
    'licence' => 'MIT',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: Cadence. Model-based scheduling for Laravel: you attach cron or RRULE schedules to any Eloquent model, and Cadence fires events when they are due. https://github.com/DirectoryTree/Cadence'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-25',
    'added' => '2026-10-26',
];
