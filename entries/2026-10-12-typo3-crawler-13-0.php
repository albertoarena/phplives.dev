<?php

return [
    'name' => 'TYPO3 Crawler 13.0',
    'sentence' => 'TYPO3 Crawler 13.0 adds TYPO3 14 LTS support to the extension that crawls the TYPO3 page tree, for cache warmup and indexing.',
    'details' => 'It drops support for TYPO3 12 LTS and PHP 8.1, and adds PHP 8.5. The release has several breaking changes and a security fix that switches from serialized data to JSON.',
    'makers' => [
        ['name' => 'Tomas Norre Mikkelsen', 'github' => 'tomasnorre'],
    ],
    'shipped' => '2026-10-12',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/tomasnorre/crawler/releases/tag/13.0.0',
    ],
    'source' => 'https://github.com/tomasnorre/crawler',
    'package' => 'tomasnorre/crawler',
    'licence' => 'GPL-3.0',
    'docs' => 'https://docs.typo3.org/p/tomasnorre/crawler/main/en-us/',
    'stack' => 'php',
    'topics' => ['cms'],
    'devnull' => 'Shipped with PHP: TYPO3 Crawler 13.0. TYPO3 Crawler 13.0 adds TYPO3 14 LTS support to the extension that crawls the TYPO3 page tree, for cache warmup and indexing. https://github.com/tomasnorre/crawler'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-10',
    'added' => '2026-11-10',
];
