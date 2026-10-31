<?php

return [
    'name' => 'Laravel Webhook Server 4.0',
    'sentence' => 'A new major version of Spatie\'s package for sending webhooks from Laravel apps, now built on the Laravel HTTP client.',
    'details' => 'Version 4 signs the exact body it sends and reuses it on every retry. It adds a stable Webhook-Id header to spot repeated deliveries, and optional retries on chosen HTTP statuses. Tests can use Http::fake().',
    'makers' => [
        ['name' => 'Spatie', 'github' => 'spatie'],
    ],
    'shipped' => '2026-10-20',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/spatie/laravel-webhook-server/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/spatie/laravel-webhook-server',
    'package' => 'spatie/laravel-webhook-server',
    'licence' => 'MIT',
    'docs' => 'https://spatie.be/docs/laravel-webhook-server/v4/introduction',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: Laravel Webhook Server 4.0. A new major version of Spatie\'s package for sending webhooks from Laravel apps, now built on the Laravel HTTP client. https://github.com/spatie/laravel-webhook-server'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-30',
    'added' => '2026-10-31',
];
