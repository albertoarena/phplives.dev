<?php

return [
    'name' => 'Route 7.0',
    'sentence' => 'A new major version of Route by The PHP League, a fast PSR-7 router with PSR-15 middleware, built on top of FastRoute.',
    'details' => 'Version 7.0 needs PHP 8.3 or later. It adds Router::match() to test a route without running its handler, and reverse routing from named routes. Its route cache now recovers by itself when it is corrupt.',
    'makers' => [
        ['name' => 'Phil Bennett', 'github' => 'philipobenito'],
        ['name' => 'The PHP League', 'github' => 'thephpleague'],
    ],
    'shipped' => '2026-07-14',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/thephpleague/route/releases/tag/7.0.0',
    ],
    'source' => 'https://github.com/thephpleague/route',
    'package' => 'league/route',
    'licence' => 'MIT',
    'docs' => 'https://route.thephpleague.com',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Route 7.0. A new major version of Route by The PHP League, a fast PSR-7 router with PSR-15 middleware, built on top of FastRoute. https://github.com/thephpleague/route'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-25',
    'added' => '2026-10-26',
];
