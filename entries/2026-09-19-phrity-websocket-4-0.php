<?php

return [
    'name' => 'Phrity Websocket 4.0',
    'sentence' => 'A new major version of Phrity Websocket, a WebSocket client and server for PHP, with a clearer set of exceptions.',
    'details' => 'Version 4 removes deprecated code and uses Nyholm PSR HTTP directly instead of its own stub classes. A server now returns its connections as a collection. A migration guide covers the move from version 3.',
    'makers' => [
        ['name' => 'Sören Jensen', 'github' => 'sirn-se'],
    ],
    'shipped' => '2026-09-19',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/sirn-se/websocket-php/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/sirn-se/websocket-php',
    'package' => 'phrity/websocket',
    'licence' => 'ISC',
    'docs' => 'https://phrity.sirn.se/websocket',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Phrity Websocket 4.0. A new major version of Phrity Websocket, a WebSocket client and server for PHP, with a clearer set of exceptions. https://github.com/sirn-se/websocket-php'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-30',
    'added' => '2026-10-31',
];
