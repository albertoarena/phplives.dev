<?php

return [
    'name' => 'Redis Object Cache 3.0',
    'sentence' => 'A new major version of Redis Object Cache, a WordPress plugin that keeps the object cache in Redis.',
    'details' => 'It supports Predis, PhpRedis and Relay, with replication, sentinels and clustering. Version 3.0 allows Predis v3. It also fixes several connection bugs.',
    'makers' => [
        ['name' => 'Till Krüss', 'github' => 'tillkruss'],
        ['name' => 'Rhubarb Group', 'github' => 'rhubarbgroup'],
    ],
    'shipped' => '2026-09-18',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/rhubarbgroup/redis-cache/releases/tag/3.0.0',
    ],
    'source' => 'https://github.com/rhubarbgroup/redis-cache',
    'package' => 'rhubarbgroup/redis-cache',
    'licence' => 'GPL-3.0-or-later',
    'docs' => 'https://wordpress.org/plugins/redis-cache/',
    'stack' => 'wordpress',
    'devnull' => 'Shipped with PHP: Redis Object Cache 3.0. A new major version of Redis Object Cache, a WordPress plugin that keeps the object cache in Redis. https://github.com/rhubarbgroup/redis-cache'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-23',
    'added' => '2026-10-23',
];
