<?php

return [
    'name' => 'Nette Caching 4.0',
    'sentence' => 'A new major version of Nette Caching, with a native Redis storage that lets only one process build a missing item while the others wait.',
    'details' => 'Any PSR-16 cache can now serve as a Nette storage. You can describe dependencies with a typed object, and a mistake in dependencies or in invalidation now throws an exception. Version 4 needs PHP 8.4.',
    'makers' => [
        ['name' => 'David Grudl', 'github' => 'dg'],
    ],
    'shipped' => '2026-11-06',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/nette/caching/releases/tag/v4.0.0',
    ],
    'source' => 'https://github.com/nette/caching',
    'package' => 'nette/caching',
    'docs' => 'https://doc.nette.org/en/caching',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: Nette Caching 4.0. A new major version of Nette Caching, with a native Redis storage that lets only one process build a missing item while the others wait. https://github.com/nette/caching'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-10',
    'added' => '2026-11-10',
];
