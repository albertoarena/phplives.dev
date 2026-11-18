<?php

return [
    'name' => 'Laravel Database MCP 1.0',
    'sentence' => 'The first stable version of Laravel Database MCP, which lets an AI assistant read a Laravel app\'s database through structured queries, never raw SQL.',
    'details' => 'The assistant gets two tools: describe_database and query_database. You can hide tables and columns from it, or mask sensitive columns. Version 1.0 adds masking, an audit log and a query timeout. It also fixes a security bug that let the assistant read hidden tables.',
    'makers' => [
        ['name' => 'Alberto Peripolli', 'github' => 'trippo'],
        ['name' => 'Datomatic', 'github' => 'datomatic'],
    ],
    'shipped' => '2026-11-15',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/datomatic/laravel-database-mcp/releases/tag/v1.0.0',
    ],
    'source' => 'https://github.com/datomatic/laravel-database-mcp',
    'package' => 'datomatic/laravel-database-mcp',
    'licence' => 'MIT',
    'stack' => 'laravel',
    'topics' => ['ai', 'mcp', 'database'],
    'devnull' => 'Shipped with PHP: Laravel Database MCP 1.0. The first stable version of Laravel Database MCP, which lets an AI assistant read a Laravel app\'s database through structured queries, never raw SQL. https://github.com/datomatic/laravel-database-mcp'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-18',
    'added' => '2026-11-18',
];
