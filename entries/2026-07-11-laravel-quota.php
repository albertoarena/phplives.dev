<?php

return [
    'name' => 'Laravel Quota',
    'sentence' => 'Usage quotas for Laravel: it tracks and enforces limits per day, week, month or a custom period, such as monthly export budgets or AI credits.',
    'details' => 'Laravel\'s RateLimiter throttles requests. Laravel Quota manages usage budgets over calendar periods instead. It keeps quotas in the cache or the database, attaches them to Eloquent models and protects routes with middleware.',
    'makers' => [
        ['name' => 'Md Mahedi Zaman Zaber', 'github' => 'zaber-dev'],
    ],
    'shipped' => '2026-07-11',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/zaber-dev/laravel-quota/releases/tag/v1.0.0',
    ],
    'source' => 'https://github.com/zaber-dev/laravel-quota',
    'package' => 'zaber-dev/laravel-quota',
    'licence' => 'MIT',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: Laravel Quota. Usage quotas for Laravel: it tracks and enforces limits per day, week, month or a custom period, such as monthly export budgets or AI credits. https://github.com/zaber-dev/laravel-quota'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-25',
    'added' => '2026-10-26',
];
