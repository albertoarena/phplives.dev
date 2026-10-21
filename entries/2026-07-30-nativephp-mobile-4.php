<?php

return [
    'name' => 'NativePHP Mobile v4',
    'sentence' => 'A new major version of NativePHP Mobile, which builds iOS and Android apps with PHP, now with a native-first boot and SuperNative.',
    'details' => 'Apps now boot native first: no WebView starts until a web route renders, on Android and on iOS. Embedded webviews on iOS get their own PHP runtimes. Plugins can extend the test suite.',
    'makers' => [
        ['name' => 'NativePHP', 'github' => 'NativePHP'],
    ],
    'shipped' => '2026-07-30',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/NativePHP/mobile-air/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/NativePHP/mobile-air',
    'package' => 'nativephp/mobile',
    'licence' => 'MIT',
    'docs' => 'https://nativephp.com/docs/mobile/4/getting-started/introduction',
    'stack' => 'nativephp',
    'devnull' => 'Shipped with PHP: NativePHP Mobile v4. A new major version of NativePHP Mobile, which builds iOS and Android apps with PHP, now with a native-first boot and SuperNative. https://github.com/NativePHP/mobile-air'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-21',
];
