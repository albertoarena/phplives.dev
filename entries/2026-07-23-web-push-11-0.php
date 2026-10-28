<?php

return [
    'name' => 'WebPush 11.0',
    'sentence' => 'A new major version of WebPush, the library that sends Web Push notifications from PHP, which now works with any PSR-18 HTTP client.',
    'details' => 'WebPush now depends on standard interfaces instead of Guzzle. If Guzzle is already installed and you pass no client arguments, it finds Guzzle and needs no code changes. The old timeout and client options in the constructor are replaced.',
    'makers' => [
        ['name' => 'Louis Lagrange', 'github' => 'Minishlink'],
    ],
    'shipped' => '2026-07-23',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/web-push-libs/web-push-php/releases/tag/v11.0.0',
    ],
    'source' => 'https://github.com/web-push-libs/web-push-php',
    'package' => 'minishlink/web-push',
    'licence' => 'MIT',
    'stack' => 'php',
    'topics' => ['api'],
    'devnull' => 'Shipped with PHP: WebPush 11.0. A new major version of WebPush, the library that sends Web Push notifications from PHP, which now works with any PSR-18 HTTP client. https://github.com/web-push-libs/web-push-php'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-28',
    'added' => '2026-10-28',
];
