<?php

return [
    'name' => 'Action Scheduler 4.0',
    'sentence' => 'A new major version of Action Scheduler, a job queue for background tasks in WordPress, made to ship inside plugins with no server access needed.',
    'details' => 'Every month it processes millions of payments for WooCommerce Subscriptions. Version 4.0 takes the action args into account when it schedules unique actions. It also purges failed actions after 3 months by default.',
    'makers' => [
        ['name' => 'Automattic', 'github' => 'Automattic'],
    ],
    'shipped' => '2026-06-16',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/woocommerce/action-scheduler/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/woocommerce/action-scheduler',
    'package' => 'woocommerce/action-scheduler',
    'licence' => 'GPL-3.0-or-later',
    'docs' => 'https://actionscheduler.org',
    'stack' => 'wordpress',
    'devnull' => 'Shipped with PHP: Action Scheduler 4.0. A new major version of Action Scheduler, a job queue for background tasks in WordPress, made to ship inside plugins with no server access needed. https://github.com/woocommerce/action-scheduler'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-25',
    'added' => '2026-10-26',
];
