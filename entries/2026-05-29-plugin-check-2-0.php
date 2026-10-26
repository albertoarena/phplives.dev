<?php

return [
    'name' => 'Plugin Check 2.0',
    'sentence' => 'A new major version of Plugin Check, a WordPress.org tool that checks whether a plugin meets the plugin directory rules and follows best practices.',
    'details' => 'It runs from a WP Admin screen or with the WP-CLI command wp plugin check. Version 2.0 flags WordPress functions that are newer than the minimum WordPress version a plugin declares. It also flags plugins that save data in their own folder.',
    'makers' => [
        ['name' => 'WordPress', 'github' => 'WordPress'],
    ],
    'shipped' => '2026-05-29',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/WordPress/plugin-check/releases/tag/2.0.0',
    ],
    'source' => 'https://github.com/WordPress/plugin-check',
    'licence' => 'GPL-2.0-or-later',
    'stack' => 'wordpress',
    'devnull' => 'Shipped with PHP: Plugin Check 2.0. A new major version of Plugin Check, a WordPress.org tool that checks whether a plugin meets the plugin directory rules and follows best practices. https://github.com/WordPress/plugin-check'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-25',
    'added' => '2026-10-26',
];
