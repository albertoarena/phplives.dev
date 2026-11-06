<?php

return [
    'name' => 'Gotenberg PHP client 3.0',
    'sentence' => 'A new major version of the PHP client for Gotenberg, an API that converts documents to PDF, rebuilt around stages for Gotenberg 9.',
    'details' => 'Each Gotenberg route is now a stage. You can run one stage, or a list of stages, on the PDFs you upload. New methods read the info, metadata, bookmarks, attachments and forms of a PDF. Version 3 needs PHP 8.3 or later.',
    'makers' => [
        ['name' => 'Julien Neuhart', 'github' => 'gulien'],
    ],
    'shipped' => '2026-11-05',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/gotenberg/gotenberg-php/releases/tag/v3.0.0',
    ],
    'source' => 'https://github.com/gotenberg/gotenberg-php',
    'package' => 'gotenberg/gotenberg-php',
    'licence' => 'MIT',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Gotenberg PHP client 3.0. A new major version of the PHP client for Gotenberg, an API that converts documents to PDF, rebuilt around stages for Gotenberg 9. https://github.com/gotenberg/gotenberg-php'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-06',
    'added' => '2026-11-06',
];
