<?php

return [
    'name' => 'ActivityPub 9.0',
    'sentence' => 'A new major version of ActivityPub, the WordPress plugin that lets people on Mastodon and other fediverse apps follow your site.',
    'details' => 'Version 9.0 adds a setting that controls how quickly posts reach followers. Images get a blurred preview that other fediverse apps can show while the images load. The release also has ten security fixes.',
    'makers' => [
        ['name' => 'Matthias Pfefferle', 'github' => 'pfefferle'],
        ['name' => 'Automattic', 'github' => 'Automattic'],
    ],
    'shipped' => '2026-06-11',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/Automattic/wordpress-activitypub/releases/tag/9.0.0',
    ],
    'source' => 'https://github.com/Automattic/wordpress-activitypub',
    'licence' => 'MIT',
    'stack' => 'wordpress',
    'topics' => ['social'],
    'devnull' => 'Shipped with PHP: ActivityPub 9.0. A new major version of ActivityPub, the WordPress plugin that lets people on Mastodon and other fediverse apps follow your site. https://github.com/Automattic/wordpress-activitypub'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-22',
];
