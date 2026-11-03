<?php

return [
    'name' => 'php-rrule 3.0',
    'sentence' => 'A new major version of php-rrule, a light and fast library for recurring dates in PHP, based on the RFC 5545 recurrence rules.',
    'details' => 'Version 3.0 drops PHP versions older than 7.3 and adds PHP 8.4. It fixes a time zone conversion with DateTimeImmutable, and a case where occursAt failed with Carbon 3.',
    'makers' => [
        ['name' => 'Rémi Lanvin', 'github' => 'rlanvin'],
    ],
    'shipped' => '2026-07-29',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/rlanvin/php-rrule/releases/tag/v3.0.0',
    ],
    'source' => 'https://github.com/rlanvin/php-rrule',
    'package' => 'rlanvin/php-rrule',
    'licence' => 'MIT',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: php-rrule 3.0. A new major version of php-rrule, a light and fast library for recurring dates in PHP, based on the RFC 5545 recurrence rules. https://github.com/rlanvin/php-rrule'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-03',
    'added' => '2026-11-03',
];
