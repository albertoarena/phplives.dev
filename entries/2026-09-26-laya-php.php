<?php

return [
    'name' => 'laya-php',
    'sentence' => 'A PHP SDK that sorts text into typed answers in more than 100 languages, on your own server and without paying for an LLM API.',
    'details' => 'It sends text to laya-serve, the HTTP server of the Laya decision engine, which you run yourself. Answers come back as typed enums, ints and bools, each with a confidence score. A built-in fake lets you test without a server.',
    'makers' => [
        ['name' => 'Marc Reichel', 'github' => 'marcreichel'],
    ],
    'shipped' => '2026-09-26',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/marcreichel/laya-php/releases/tag/0.0.1',
    ],
    'source' => 'https://github.com/marcreichel/laya-php',
    'package' => 'marcreichel/laya-php',
    'licence' => 'Apache-2.0',
    'stack' => 'php',
    'topics' => ['ai', 'self-hosted'],
    'devnull' => 'Shipped with PHP: laya-php. A PHP SDK that sorts text into typed answers in more than 100 languages, on your own server and without paying for an LLM API. https://github.com/marcreichel/laya-php'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-30',
    'added' => '2026-10-31',
];
