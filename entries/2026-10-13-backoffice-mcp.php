<?php

return [
    'name' => 'Back-office MCP',
    'sentence' => 'A Laravel package that lets an AI agent read your app\'s database through a strict, read-only whitelist of Eloquent models.',
    'details' => 'It is meant for internal tools, such as an admin server or a support server. Each server gives the agent three tools: schema, query and count. The agent cannot reach anything that is not on the whitelist.',
    'makers' => [
        ['name' => 'Matthieu Napoli', 'github' => 'mnapoli'],
    ],
    'shipped' => '2026-10-13',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/mnapoli/backoffice-mcp/releases/tag/0.1.0',
    ],
    'source' => 'https://github.com/mnapoli/backoffice-mcp',
    'package' => 'mnapoli/backoffice-mcp',
    'licence' => 'MIT',
    'stack' => 'laravel',
    'topics' => ['ai', 'mcp', 'database'],
    'devnull' => 'Shipped with PHP: Back-office MCP. A Laravel package that lets an AI agent read your app\'s database through a strict, read-only whitelist of Eloquent models. https://github.com/mnapoli/backoffice-mcp'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-12',
    'added' => '2026-11-12',
];
