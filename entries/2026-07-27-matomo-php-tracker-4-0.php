<?php

return [
    'name' => 'Matomo PHP Tracker 4.0',
    'sentence' => 'A new major version of the PHP client for the Matomo Analytics tracking API, with strict types and safer defaults.',
    'details' => 'It needs PHP 8.1 or later. The default request timeouts drop from minutes to seconds, so a slow Matomo server cannot block your page. It can also track requests from known AI bots.',
    'makers' => [
        ['name' => 'Matomo', 'github' => 'matomo-org'],
    ],
    'shipped' => '2026-07-27',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/matomo-org/matomo-php-tracker/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/matomo-org/matomo-php-tracker',
    'package' => 'matomo/matomo-php-tracker',
    'licence' => 'BSD-3-Clause',
    'stack' => 'php',
    'topics' => ['api'],
    'devnull' => 'Shipped with PHP: Matomo PHP Tracker 4.0. A new major version of the PHP client for the Matomo Analytics tracking API, with strict types and safer defaults. https://github.com/matomo-org/matomo-php-tracker'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-03',
    'added' => '2026-11-03',
];
