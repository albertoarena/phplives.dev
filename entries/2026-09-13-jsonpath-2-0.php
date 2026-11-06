<?php

return [
    'name' => 'JSONPath 2.0',
    'sentence' => 'A new major version of JSONPath for PHP, which searches and flattens arrays, with results that now follow the JSONPath comparison consensus.',
    'details' => 'Selector parsing is stricter, so malformed or unclear syntax is now rejected. Filters use only portable comparison operators. The minimum version is now PHP 8.3. The tests still cover every line.',
    'makers' => [
        ['name' => 'Sascha Greuel', 'github' => 'SoftCreatR'],
    ],
    'shipped' => '2026-09-13',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/SoftCreatR/JSONPath/releases/tag/2.0.0',
    ],
    'source' => 'https://github.com/SoftCreatR/JSONPath',
    'package' => 'softcreatr/jsonpath',
    'licence' => 'MIT',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: JSONPath 2.0. A new major version of JSONPath for PHP, which searches and flattens arrays, with results that now follow the JSONPath comparison consensus. https://github.com/SoftCreatR/JSONPath'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-06',
    'added' => '2026-11-06',
];
