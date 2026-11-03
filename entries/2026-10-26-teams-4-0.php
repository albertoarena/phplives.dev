<?php

return [
    'name' => 'Teams 4.0',
    'sentence' => 'A new major version of Teams, a Laravel package for teams with their own roles, groups and permissions, now with a fluent API.',
    'details' => 'Team permissions now work with Laravel\'s Gate, so $user->can(), @can and the can middleware all understand them. Invitation links expire after 7 days. It needs PHP 8.3 and Laravel 10.30 or later.',
    'makers' => [
        ['name' => 'Yuri Gerasimov', 'github' => 'Jurager'],
    ],
    'shipped' => '2026-10-26',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/Jurager/teams/releases/tag/4.0',
    ],
    'source' => 'https://github.com/Jurager/teams',
    'package' => 'jurager/teams',
    'licence' => 'MIT',
    'docs' => 'https://docs.gerassimov.me/teams/',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: Teams 4.0. A new major version of Teams, a Laravel package for teams with their own roles, groups and permissions, now with a fluent API. https://github.com/Jurager/teams'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-03',
    'added' => '2026-11-03',
];
