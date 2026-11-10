<?php

return [
    'name' => 'PHPFlow',
    'sentence' => 'A static flow analyzer for Symfony apps that traces routes and Messenger messages down to database effects and HTTP calls, without running the app.',
    'details' => 'It can build an HTML viewer to explore the flows of an app, with search and filters. It also works backwards: it shows which entry points a change to a table, a service or a message would affect. It exports JSON and Mermaid.',
    'makers' => [
        ['name' => 'Patrick', 'github' => 'patryyyck'],
    ],
    'shipped' => '2026-08-30',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/patryyyck/phpflow/releases/tag/v0.1.0',
    ],
    'source' => 'https://github.com/patryyyck/phpflow',
    'licence' => 'MIT',
    'stack' => 'symfony',
    'topics' => ['devtools'],
    'devnull' => 'Shipped with PHP: PHPFlow. A static flow analyzer for Symfony apps that traces routes and Messenger messages down to database effects and HTTP calls, without running the app. https://github.com/patryyyck/phpflow'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-10',
    'added' => '2026-11-10',
];
