<?php

return [
    'name' => 'Laravel LSP',
    'sentence' => 'A language server that gives editors completions, hover info, diagnostics and Go to Definition for Laravel and Blade code.',
    'details' => 'It is installed with composer global require. It talks to the editor over stdio. The README shows how to set it up in VS Code, Cursor, Sublime Text, Zed, Neovim and OpenCode.',
    'makers' => [
        ['name' => 'Laravel', 'github' => 'laravel'],
    ],
    'shipped' => '2026-05-14',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/laravel/lsp/releases/tag/v0.0.4',
    ],
    'source' => 'https://github.com/laravel/lsp',
    'package' => 'laravel/lsp',
    'stack' => 'laravel',
    'topics' => ['devtools'],
    'devnull' => 'Shipped with PHP: Laravel LSP. A language server that gives editors completions, hover info, diagnostics and Go to Definition for Laravel and Blade code. https://github.com/laravel/lsp'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-23',
    'added' => '2026-10-23',
];
