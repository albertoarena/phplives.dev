<?php

return [
    'name' => 'Semantic MediaWiki 7.0',
    'sentence' => 'A new major version of Semantic MediaWiki, which lets you store and query data in wiki pages, with an easier install and faster queries.',
    'details' => 'It adds support for MediaWiki 1.45 and 1.46 and for PHP 8.5. Configuration takes plain strings instead of SMW_* constants. Install-state data moves into the database, so a setup with many servers needs no shared file storage.',
    'makers' => [
        ['name' => 'Semantic MediaWiki', 'github' => 'SemanticMediaWiki'],
    ],
    'shipped' => '2026-06-04',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/SemanticMediaWiki/SemanticMediaWiki/releases/tag/7.0.0',
    ],
    'source' => 'https://github.com/SemanticMediaWiki/SemanticMediaWiki',
    'package' => 'mediawiki/semantic-media-wiki',
    'licence' => 'GPL-2.0-or-later',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Semantic MediaWiki 7.0. A new major version of Semantic MediaWiki, which lets you store and query data in wiki pages, with an easier install and faster queries. https://github.com/SemanticMediaWiki/SemanticMediaWiki'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-22',
];
