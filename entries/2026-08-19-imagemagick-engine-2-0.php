<?php

return [
    'name' => 'ImageMagick Engine 2.0',
    'sentence' => 'A new major version of ImageMagick Engine, a WordPress plugin that resizes images with ImageMagick instead of GD for better quality.',
    'details' => 'The settings page is rebuilt. Image regeneration now runs in batches, continues after a page reload, and can be stopped and resumed. It also lists the images that failed. It needs WordPress 6.4 or later.',
    'makers' => [
        ['name' => 'Rickard Westerlind', 'github' => 'rickardw'],
        ['name' => 'Orangelab', 'github' => 'orangelabweb'],
    ],
    'shipped' => '2026-08-19',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/orangelabweb/imagemagick-engine/releases/tag/2.0.0',
    ],
    'source' => 'https://github.com/orangelabweb/imagemagick-engine',
    'licence' => 'GPL-2.0-or-later',
    'stack' => 'wordpress',
    'devnull' => 'Shipped with PHP: ImageMagick Engine 2.0. A new major version of ImageMagick Engine, a WordPress plugin that resizes images with ImageMagick instead of GD for better quality. https://github.com/orangelabweb/imagemagick-engine'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-11-03',
    'added' => '2026-11-03',
];
