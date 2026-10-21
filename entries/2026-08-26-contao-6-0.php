<?php

return [
    'name' => 'Contao 6.0',
    'sentence' => 'A new major version of Contao, the open source CMS built on Symfony, which now stores input as it is and uses Twig for all its templates.',
    'details' => 'Input is no longer encoded when it is saved: data is stored raw and encoded on output. The HTML5 templates are gone, and Twig replaces them. It is the first major version of Contao in about four and a half years.',
    'makers' => [
        ['name' => 'Contao', 'github' => 'contao'],
    ],
    'shipped' => '2026-08-26',
    'proof' => [
        'kind' => 'post',
        'url' => 'https://contao.org/en/news/contao-6-0-0-is-available',
    ],
    'source' => 'https://github.com/contao/contao',
    'licence' => 'LGPL-3.0-or-later',
    'stack' => 'symfony',
    'devnull' => 'Shipped with PHP: Contao 6.0. A new major version of Contao, the open source CMS built on Symfony, which now stores input as it is and uses Twig for all its templates. https://github.com/contao/contao'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-21',
    'added' => '2026-10-21',
];
