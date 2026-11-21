<?php

return [
    'name' => 'fast-cgi-client 4.0',
    'sentence' => 'A new major version of fast-cgi-client, a PHP library that sends requests to PHP-FPM, synchronously or asynchronously.',
    'details' => 'Version 4 requires PHP 8.0. The content of a request is now an object instead of a string, and multipart form-data requests detect the MIME type of each file. A response that cannot be read now throws an exception instead of being skipped.',
    'makers' => [
        ['name' => 'Holger Woltersdorf', 'github' => 'hollodotme'],
    ],
    'shipped' => '2026-10-05',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/hollodotme/fast-cgi-client/releases/tag/v4.0.0',
    ],
    'source' => 'https://github.com/hollodotme/fast-cgi-client',
    'package' => 'hollodotme/fast-cgi-client',
    'licence' => 'MIT',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: fast-cgi-client 4.0. A new major version of fast-cgi-client, a PHP library that sends requests to PHP-FPM, synchronously or asynchronously. https://github.com/hollodotme/fast-cgi-client'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-21',
    'added' => '2026-11-21',
];
