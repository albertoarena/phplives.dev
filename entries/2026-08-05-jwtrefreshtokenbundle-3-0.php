<?php

return [
    'name' => 'JWTRefreshTokenBundle 3.0',
    'sentence' => 'A new major version of JWTRefreshTokenBundle, which adds refresh tokens to logins with JSON Web Tokens in Symfony.',
    'details' => 'Version 3.0 supports Symfony 8, PHP 8.4 and LexikJWTAuthenticationBundle 3. Refresh tokens now belong to a family, so a whole session can be ended at once. The update needs a change to the database schema.',
    'makers' => [
        ['name' => 'Ángel Guzmán Maeso', 'github' => 'shakaran'],
        ['name' => 'Marcos Gómez Vilches', 'github' => 'markitosgv'],
    ],
    'shipped' => '2026-08-05',
    'proof' => [
        'kind' => 'release',
        'url' => 'https://github.com/markitosgv/JWTRefreshTokenBundle/releases/tag/v3.0.0',
    ],
    'source' => 'https://github.com/markitosgv/JWTRefreshTokenBundle',
    'package' => 'gesdinet/jwt-refresh-token-bundle',
    'licence' => 'MIT',
    'stack' => 'symfony',
    'topics' => ['security'],
    'devnull' => 'Shipped with PHP: JWTRefreshTokenBundle 3.0. A new major version of JWTRefreshTokenBundle, which adds refresh tokens to logins with JSON Web Tokens in Symfony. https://github.com/markitosgv/JWTRefreshTokenBundle'
        ."\n\n".'See more at https://phplives.dev',
    'checked' => '2026-10-25',
    'added' => '2026-10-26',
];
