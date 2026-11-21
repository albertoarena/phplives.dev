<?php

return [
    'name' => 'Open Graph 3.0',
    'sentence' => 'A new major version of Open Graph, the WordPress plugin that adds Open Graph metadata to your posts and pages.',
    'details' => 'Images are now found in one pass over the post content, including gallery, media, text and nested block images. A new opengraph_image_sources filter adds or removes image sources and replaces the older image filters. A post with an image now gets a large Twitter card.',
    'makers' => [
        ['name' => 'Matthias Pfefferle', 'github' => 'pfefferle'],
        ['name' => 'Will Norris', 'github' => 'willnorris'],
    ],
    'shipped' => '2026-09-18',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/pfefferle/wordpress-opengraph/releases/tag/3.0.0',
    ],
    'source' => 'https://github.com/pfefferle/wordpress-opengraph',
    'licence' => 'Apache-2.0',
    'docs' => 'https://wordpress.org/plugins/opengraph/',
    'stack' => 'wordpress',
    'topics' => ['social'],
    'devnull' => 'Shipped with PHP: Open Graph 3.0. A new major version of Open Graph, the WordPress plugin that adds Open Graph metadata to your posts and pages. https://github.com/pfefferle/wordpress-opengraph'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-21',
    'added' => '2026-11-21',
];
