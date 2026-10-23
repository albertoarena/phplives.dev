<?php

return [
    'name' => 'Checkpoint',
    'sentence' => 'A Laravel security scanner that checks an app for known CVEs, risky settings and hardcoded secrets with one Artisan command.',
    'details' => 'The command is php artisan checkpoint:scan. It runs composer audit and npm audit. It also checks .env settings and file permissions. It looks for risky code, such as raw SQL with variables or unescaped Blade output.',
    'makers' => [
        ['name' => 'Andrea Pollastri', 'github' => 'andreapollastri'],
    ],
    'shipped' => '2026-04-27',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/andreapollastri/checkpoint/releases/tag/1.0.0',
    ],
    'source' => 'https://github.com/andreapollastri/checkpoint',
    'package' => 'andreapollastri/checkpoint',
    'licence' => 'MIT',
    'docs' => 'https://checkpoint.web.ap.it',
    'stack' => 'laravel',
    'devnull' => 'Shipped with PHP: Checkpoint. A Laravel security scanner that checks an app for known CVEs, risky settings and hardcoded secrets with one Artisan command. https://github.com/andreapollastri/checkpoint'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-23',
    'added' => '2026-10-23',
];
