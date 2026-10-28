<?php

return [
    'name' => 'xmlseclibs 4.0',
    'sentence' => 'A new major version of xmlseclibs, a PHP library for XML Security, with safe defaults when it checks signatures.',
    'details' => 'Its cryptography now runs on phpseclib, with RSA-PSS support. A new verifyDocument() method pins the key and allows only listed algorithms. Several unsafe inputs are now rejected by default, and a legacy mode helps while peers migrate.',
    'makers' => [
        ['name' => 'Rob Richards', 'github' => 'robrichards'],
    ],
    'shipped' => '2026-08-08',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/robrichards/xmlseclibs/releases/tag/4.0.0',
    ],
    'source' => 'https://github.com/robrichards/xmlseclibs',
    'package' => 'robrichards/xmlseclibs',
    'licence' => 'BSD-3-Clause',
    'stack' => 'php',
    'topics' => ['security'],
    'devnull' => 'Shipped with PHP: xmlseclibs 4.0. A new major version of xmlseclibs, a PHP library for XML Security, with safe defaults when it checks signatures. https://github.com/robrichards/xmlseclibs'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-28',
    'added' => '2026-10-28',
];
