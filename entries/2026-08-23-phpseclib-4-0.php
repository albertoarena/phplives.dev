<?php

return [
    'name' => 'phpseclib 4.0',
    'sentence' => 'A new major version of phpseclib, a pure-PHP library for SSH-2, SFTP, X.509, RSA, AES and more, with new PFX and CMS support.',
    'details' => 'X509 is split into separate X509, CSR, CRL and SPKAC classes. SSH2 and SFTP now throw exceptions instead of returning false. It needs PHP 8.1 or later, and the namespace is now phpseclib4.',
    'makers' => [
        ['name' => 'phpseclib', 'github' => 'phpseclib'],
        ['name' => 'Jim Wigginton', 'github' => 'terrafrost'],
    ],
    'shipped' => '2026-08-23',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/phpseclib/phpseclib/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/phpseclib/phpseclib',
    'package' => 'phpseclib/phpseclib',
    'licence' => 'MIT',
    'docs' => 'https://phpseclib.com/',
    'stack' => 'php',
    'devnull' => 'Shipped with PHP: phpseclib 4.0. A new major version of phpseclib, a pure-PHP library for SSH-2, SFTP, X.509, RSA, AES and more, with new PFX and CMS support. https://github.com/phpseclib/phpseclib'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-22',
];
